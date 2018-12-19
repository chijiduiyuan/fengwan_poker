/*
 * 游戏房间类 - 一些房间通用管理方法
 *
 * author cxf
 *
 * versions 2017-07-21
 */
module.exports = function () {
	
	var self = this;
	
	this.gameType = '';//游戏类型 dzPoker cowWater thriDucal cowcow
	this.gameClass;
	this.roomList = {};
	this.roomUserList = {};

	this.init = function(gameType,gameClass){
		this.gameType = gameType;
		this.gameClass = gameClass;

		this.roomList = C['room_'+this.gameType];	
		this.roomUserList = C['room_'+this.gameType+'_users'];
	}

	//取战斗中游戏房间信息
	this.getRoom = function(roomId){
		return this.roomList[roomId];
	}
	//添加战斗中游戏房间信息
	this.setRoom = function(roomInfo){
		this.roomList[roomInfo.roomId] = roomInfo;
	
		//rpc调用 超时的房间id 在前端服务器房间列表中   	
		App.rpc.connector.roomRemote.addRoom(1,[roomInfo.roomId],this.gameType,App.getServerId(),function(){
			G.trace('添加 master 房间信息');
		});	  
	}

	//删除房间信息
	this.delRoom = function(roomId){
		delete this.roomList[roomId];

		//rpc调用 超时的房间id 在前端服务器房间列表中   	
		App.rpc.connector.roomRemote.delRoom(1,[roomId],this.gameType,function(){
			G.trace('删除 master 房间信息');
		});	
	}

	//取玩家正在游戏中的roomid	
	this.getUserRoomId = function(uid){
		return this.roomUserList[uid];
	}
	//设置玩家正在游戏中的roomid	
	this.setUserRoomId = function(uid,roomId){
		if( roomId ===false ){
			delete(this.roomUserList[uid]);
		}else{
			this.roomUserList[uid] = roomId;	
		}
	}
	//删除玩家的房间信息
	this.delRoomUser = function(uid){
		delete this.roomUserList[uid];
	}

	//删除过期房间 并返回删除的房间id数组	
	this.delTimeOutRoom = function(){
		var roomIdArr = [];
		//删除过期游戏实例
		var now = G.time();
		var timeout = 10*60*1000;//超时时间10分钟 超过则表示游戏实例中途异常退出
		var roomInfo;
		for(var ri in this.roomList){
			roomInfo = this.roomList[ri];
			if( now-roomInfo.startTime> timeout){		
				console.error('有游戏异常超时 删除缓存 roomId='+roomInfo.roomId);
				//删除缓存 - 玩家退出房间 			
			    for(var i in  roomInfo.upPlayers){   
			    	if(this.roomUserList[i]){
			    		this.delRoomUser(i);
			    	}		        
			    }		    
			    for(var j in  roomInfo.players){   
			        if(roomUserList[j]){
			    		this.delRoomUser(j);
			    	}	
			    }				
				this.delRoom(ri);		
			}else{			
				roomIdArr.push( roomInfo.roomId );
			}
		}
		return roomIdArr;
	}

	/**
	 * 预约增加筹码
	 */
	this.preAddBet = function(msg, session, next) {
	    var uid = session.uid;
	    var bet = msg.bet*1;
	    G.phpPost({	   
	        "mod"           :"room",
            "game"          :self.gameType,
	        "op":"preAddBet",
	        "uid":uid,
	        "bet":bet
	    },function(postRs){    
	        if(!postRs){            
	            next(null, {"code": 404});
	            console.error('post php error');
	            return;
	        }   
	        if(postRs.code!=200){            
	            next(null, {"code": postRs.code});
	            return;
	        }   
	        
	        //给所有人发送消息 增加筹码
	        self.toClient({
	           "uid":uid,
	           "bet":bet,
	           "op":"preAddBet"
	        },postRs.data.uidArr); 

	        next(null, {"code": postRs.code});

	    }); 
	}//preAddBet

	/**
	 * 公共大厅一键坐下
	 */
	this.publicInRoomSiteDown = function(msg, session, next) {
	    var uid     = session.uid;
	    G.trace('公共大厅一键坐下 UID='+uid);
	    G.phpPost({
	        "mod":"common",
	        "op":"publicInRoomSiteDown",  
	        "game":self.gameType,    
	        "uid":uid
	    },function(postRs){ 

	        if(!postRs){            
	            //非法格式
	            next(null, {"code": 404});
	            console.error('post php error');
	            console.error(postRs);
	            return;
	        }   
	        if(postRs.code!=200){            
	            next(null, {"code": postRs.code});
	            return;
	        } 

	        var curRoom = postRs.data.roomInfo;

	        //判断当前roomId 是否已经在其他进程中 进行游戏了
	        //如果在他进程中已经游戏了 则RPC调用 转移到这个游戏进程中处理

	        //php 的json转换如果是空对象会被转成数组 这里做下处理
	        if( Array.isArray(curRoom.players) ){
	            curRoom.players = {};
	        }
	        if( Array.isArray(curRoom.upPlayers) ){
	            curRoom.upPlayers = {};
	        }

	        //更新session
	        G.updateSession(session,{
	            "roomId"    : curRoom.roomId,
	            "roomType"  : 'game_'+self.gameType
	        });

	        next(null, {
	            "code": postRs.code,
	            "roomInfo":curRoom
	        });

	        var selfSid = App.getServerId();
        	//如果本进程中没有游戏实例 则去前端服务器取 是否在其他进程中 并RPC调用执行逻辑
        	App.rpc.connector.roomRemote.getRoom(1,curRoom.roomId,self.gameType,function(sid){
				G.trace('App.rpc.connector.roomRemote.getRoom');
				G.trace(sid);

				if(sid && sid != selfSid){
					//表示在其他进程中已经有这个roomId
					//则后面的请求就给指定的 server 处理
					var msgData = {
						namespace : 'user',
						serverType: self.gameType,
						service : 'roomRemote',
						method : 'publicInRoomSiteDown_remote',
						args : [uid,curRoom]
					};
					App.rpcInvoke(sid,msgData,function(rs){
						//G.trace(sid+' 远程RPC over '+rs);
						
					});
				}else{
					self.publicInRoomSiteDown_next(uid,curRoom);
				}
			});
	      

	    });
	}//publicInRoomSiteDown

	this.publicInRoomSiteDown_next = function(uid,curRoom){
		G.trace(' next BEGIN sid='+App.getServerId() );
		//是否是游戏中            
        var roomInfo =  this.getRoom(curRoom.roomId);
        //如果游戏中则把玩家插入到游戏实例中 这样后续游戏过程就会收到
        if( roomInfo ){  
            G.trace('如果游戏中则把玩家插入到游戏实例中+++++++++++++');           
            roomInfo.upPlayers[uid] = G.clone(curPlayer);
            //进入已经开始的游戏中 需要加入缓存	            
            self.setUserRoomId(uid,curRoom.roomId);

        }
		//告诉其他玩家有新玩家在房间内坐下          
        var uidArr = [];
        for(var i in curRoom.players){
            if(i==uid || curRoom.players.standUp){
                continue;
            }
            uidArr.push( curRoom.players[i].uid );
        }
        for(var j in curRoom.upPlayers){
            if(j==uid){
                continue;
            }
            uidArr.push( curRoom.upPlayers[j].uid );
        }
        var curPlayer = {};
        if(uidArr.length>0){
            //告诉房间内玩家信息      
            
            if(curRoom.players[uid]){ //坐下列表中
                curPlayer = curRoom.players[uid];

                self.toClient({
                   "uid":uid,
                   "player" : curPlayer,
                   "op":"siteDown"
                },uidArr); 
                //如果未开始游戏
                if( !roomInfo ){  
                    //如果坐下玩家人数满足则直接开始游戏        
                    self.nextStartByRoomId(curRoom.roomId);
                }
                
            }else if(curRoom.upPlayers[uid]){//旁观列表中
                curPlayer = curRoom.upPlayers[uid];
                self.toClient({           
                   "op":"inRoom",
                   "player":curPlayer
                },uidArr); 
            }

        }		

        G.trace(' next OVER sid='+App.getServerId() );
	}//publicInRoomSiteDown_next

	/**
	 * 进入房间
	 */
	this.in = function(msg, session, next) {

	    var uid     = session.uid;

	    G.trace('进入房间 UID='+uid+' roomId='+msg.roomId);
	    G.phpPost({
	        "mod"           :"room",
            "game"          :self.gameType,
	        "op":"inRoom",    
	        "uid":uid,
	        "roomId":msg.roomId,
	        "pw":msg.pw
	    },function(postRs){ 
	        if(!postRs){            
	            //非法格式
	            next(null, {"code": 404});
	            console.error('post php error');
	            console.error(postRs);
	            return;
	        }   
	        if(postRs.code==225){    //已在房间中        
	        	G.trace('已在房间中 uid='+uid);
	            postRs.code = 200;
	            postRs.data = postRs.msg;
	        }else if(postRs.code!=200){            
	            next(null, {"code": postRs.code});
	            return;
	        }

	        var curRoom = postRs.data.roomInfo;
	        //php 的json转换如果是空对象会被转成数组 这里做下处理
	        if( Array.isArray(curRoom.players) ){
	            curRoom.players = {};
	        }
	        if( Array.isArray(curRoom.upPlayers) ){
	            curRoom.upPlayers = {};
	        }

	        //更新session
	        G.updateSession(session,{
	            "roomId"    : curRoom.roomId,
	            "roomType"  : 'game_'+self.gameType
	        });

	        next(null, {
	            "code": postRs.code,
	            "roomInfo":curRoom
	        });
	        
	        //告诉其他玩家有新玩家进入房间          
	        var uidArr = [];
	        for(var i in curRoom.players){
	            if(i==uid || curRoom.players.standUp){
	                continue;
	            }
	            uidArr.push( curRoom.players[i].uid );
	        }
	        for(var j in curRoom.upPlayers){
	            if(j==uid){
	                continue;
	            }
	            uidArr.push( curRoom.upPlayers[j].uid );
	        }
	        
	        if(uidArr.length>0){
	            self.toClient({           
	               "op":"inRoom",
	               "player":curRoom.upPlayers[uid]
	            },uidArr); 
	        }
	     

	        self.setUserRoomId(uid, curRoom.roomId);

	        //如果游戏中则把玩家插入到游戏实例中                 
	        var roomInfo =  self.getRoom(curRoom.roomId);

	        if( roomInfo ){  //游戏已开始 则弃牌后才能站起
	            G.trace('如果游戏中则把玩家插入到游戏实例中+++++++++++++');  
	            //进入已经开始的游戏中 需要加游戏实例中	            
	            roomInfo.upPlayers[uid] = G.clone(curRoom.upPlayers[uid]);
	                     
	        }else{
	        	G.trace('---------------------');
	        }

	    });

	}//in

	/**
	 * 坐下
	 */
	this.siteDown = function(msg, session, next) {
	    var uid     = session.uid;
	    var site    = msg.site*1;//座位


	    G.phpPost({
	        "mod"           :"room",
            "game"          :self.gameType,
	        "op":"siteDown",
	        "uid":uid,
	        "site":site//座位序号 0-8
	    },function(postRs){    
	        if(!postRs){                        
	            next(null, {"code": 404});
	            console.error('siteDown post php error');
	            G.trace(postRs);
	            return;
	        }   
	        if(postRs.code!=200){            
	            next(null, {"code": postRs.code});
	            return;
	        }   
	        next(null, {"code": postRs.code});   

	        //告诉房间内玩家信息        
	        self.toClient({
	           "uid":uid,
	           "player" : postRs.data.playerInfo,
	           "op":"siteDown"
	        },postRs.data.uidArr); 


	        //如果游戏未开且
	        //如果坐下玩家人数满足则直接开始游戏        
	        
	        self.nextStartByRoomId(postRs.data.roomId);
	        
	    });    

	}//siteDown

	/**
	 * 开始游戏 代理开局
	 */
	this.startGame = function(msg, session, next) {
	    var uid     = session.uid;
	    G.phpPost({
	        "mod"           :"room",
            "game"          :self.gameType,
	        "op":"startGame",
	        "uid":uid
	    },function(postRs){    
	        if(!postRs){            
	            next(null, {"code": 404});
	            console.error('post php error');
	            return;
	        }   
	        if(postRs.code!=200){            
	            next(null, {"code": postRs.code});
	            return;
	        }   

	        var roomInfo = postRs.data.roomInfo;
	        if( Array.isArray(roomInfo.players) ){
	            roomInfo.players = {};
	        }
	        if( Array.isArray(roomInfo.upPlayers) ){
	            roomInfo.upPlayers = {};
	        }

	        self.nextStartByRoomId(roomInfo.roomId,0);

	        next(null, {"code": postRs.code});
	    }); 
	}//startGame

	/**
	 * 根据uid查找房间信息
	 */
	this.getRoomByUID = function(uid){

	    var roomId = this.getUserRoomId(uid);
	    if(!roomId){
	        G.trace('玩家不在房间内:');
	        return '玩家不在房间内';
	    }

	    var roomInfo =  this.getRoom(roomId);
	    if( !roomInfo ){
	    	this.setUserRoomId(uid,false);//房间不存在 删除玩家缓存
	        G.trace('房间不存在:'+roomId);
	        return '房间不存在';
	    }

	    return roomInfo;
	}//getRoomByUID


	/**
	 * 根据房间id开始游戏 延迟5秒
	 */
	this.nextStartByRoomId = function(roomId,ts){
	    G.trace('nextStartByRoomId='+roomId);

	    //已经在游戏过程中则无法再开始游戏    
	    if( this.getRoom(roomId) ){
	        G.trace('已经在游戏过程中则无法再开始游戏');
	        return false;
	    }
	    var ts = ts||CFG['game_'+this.gameType].siteDowStartTimeout;

	    //var ts = ts||CFG.game_dzPoker.siteDowStartTimeout;

	    if( C['timeroutArr'][roomId]  //已经开始了
	        && (G.time()-C['timeroutArr'][roomId]<60*10)  //10分钟内才有效 超过时间表示异常 解除锁
	    ){
	        G.trace('下一场游戏已经准备中... '+ C['timeroutArr'][roomId]);    
	        return false;
	    }
	    C['timeroutArr'][roomId] =G.time();

	    setTimeout(function(){

 			C['roomPlayRequesting'][roomId] =G.time();
	    	
	        G.phpPost({
	            "mod"       :"room",
	            "game"      :self.gameType,
	            "op"		:"play",
	            "roomId"	:roomId
	        },function(postRs){    
	            delete(C['timeroutArr'][roomId]);
	            delete(C['roomPlayRequesting'][roomId]);
	            if(!postRs){                            
	                console.error('post php error');
	                return false;
	            }   
	            if(postRs.code!=200){            	                
	                console.error('code'+postRs.code);
	                return false;
	            }   

	            var roomInfo = postRs.data.roomInfo;
	           
	            self.startGameByRoom(roomInfo);
	        });        
	    },ts);
	}//nextStartByRoomId



	/**
	 * 直接开始游戏 定时器直接开始
	 */
	this.startGameByRoom = function(roomInfo){
	    
	    if(roomInfo.status!=1){
	        G.trace('未开局,无法开始游戏');
	        return false;
	    }
	 
	    var playerNum = 0;
	    for(var j in  roomInfo.players){           
	        playerNum++;
	    }
	    if(playerNum<2){//游戏人数足够直接开始        
	        G.trace('人数不足 无法开始游戏 '+playerNum);      
	        return false;
	    }

	    roomInfo.opArr = [];
	    roomInfo.startTime = G.time();//记录本手开始时间用于房间异常超时处理
	    //加入本进程房间列表  用于游戏引擎使用        
	    this.setRoom(roomInfo);
	    //缓存 表示玩家在房间中 
	    for(var i in  roomInfo.upPlayers){   	        
	        this.setUserRoomId(i,roomInfo.roomId);
	    }
	    
	    for(var j in  roomInfo.players){   	        
	        this.setUserRoomId(j,roomInfo.roomId);    
	    }
	    //G.trace('准备开始游戏');     
	    //setTimeout(function(){
	        G.trace('开始游戏--------------------------');     
	        var gameObj = new self.gameClass();  //不同游戏调用不同引擎
	      
	        gameObj.init(roomInfo);
	   // },1000);
	   
	    return true;
	}//startGameByRoom

	//发送给客户端信息
	this.toClient = function(data,uidArr){
	    G.trace('toClient');
	    G.trace(uidArr);
	
	    if(!Array.isArray(uidArr)){
	        uidArr = [uidArr];
	    }
	   
	    data['mod'] = 'game_'+this.gameType;
	    for( var i in uidArr){
	  
	        G.trace('onGame_'+this.gameType+':'+uidArr[i]);
	        App.get('channelService').pushMessageByUids('onData', data, [{'uid':uidArr[i], 'sid':G.frontendId}], function(){});
	    }
	}//toClient
}

