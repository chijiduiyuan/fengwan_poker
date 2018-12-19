/**
 * 玩家连接服务器模块
 *
 * author HJH
 * versions 2015-01-10
 */


var Handler = module.exports;
C['kickUids'] = {};
//php登陆模拟数据
// var userIndex = 0;
// var userList = {

// }

/**
 * php登陆
 */
// Handler.loginPHP = function(msg, session, next) {

// 	userIndex++;

// 	var userObj = {
// 		'uid' : userIndex,
// 		'nickname' : '玩家'+userIndex,
// 		'avatar' : 'avatar'+userIndex,
// 		'bet' : 1000,

// 		'token' : 'pw',
// 	}

// 	Redis.hmset('user:'+userIndex,userObj,function(err){
	
// 		console.log('loginPHP ok');
// 		console.log(userObj);

// 		next(null, {code: 200});
// 	})


// }

/**
 * node登陆进入游戏
 */
Handler.loginServer = function(msg, session, next) {
	
	var uid = msg.uid;
	var token = msg.token;
	var sessionId = msg.sessionId;

	var userInfo = {};
	var dataInfo = {};

	if(session.uid){
		G.trace('已经在线不能重复登陆');
		next(null,{code:404});
		return;

	}

	var funAry = [

		function(cb) {
			G.phpPost({
		        "mod"	:'common',
		        "op"	:'nodeLogin',
		        "sessionId": sessionId,
			    "uid"	:uid,
			    "token"	:token
		    },function(postRs){ 
		    	if(postRs.code!=200){
		    		cb(postRs.code);
		    		return;
		    	}
		    	dataInfo = postRs.data;
		    	cb();
			});
	
		},
		function(cb) {
			
			if(C.kickUids[uid]){ //踢下线标志复位
				delete(C.kickUids[uid]);
			}

			var sessionService = App.get('sessionService');
			sessionService.singleSession = true;
			
			var sessionArr = sessionService.getByUid(uid);
			//是否已登录
			if( !! sessionArr ) {
				C.kickUids[uid] = uid;
				G.trace('C.kickUids[uid]  ='+C.kickUids[uid] );

				//告诉原来的玩家重复登录通知
				var channelObj = G.getCH_obj();
				msgData = {
				    "op":"login_repeat",
				    "data":{}
				};

				G.trace('告诉原来的玩家重复登录通知 uid='+uid);
				channelObj.pushMessageByUids('onMsg', msgData, [{'uid':uid, 'sid':G.frontendId}], function(){});

				//剔除原用户下线
				sessionService.kick(uid, function() {
					G.trace('剔除原用户下线 uid='+uid);					
					cb();
				});

			}else{
				cb();
			}
		},
		function(cb) {
			console.log('绑定uid到session');
			//绑定uid到session
			session.bind(uid);
			
			dataInfo.userInfo['nodeToken'] = token;
			//标志玩家当前在哪个游戏房间内	
			G.trace(dataInfo);	
			dataInfo.userInfo['roomId'] = 0;
			dataInfo.userInfo['roomType'] = '';
			
			session.set('userInfo', dataInfo.userInfo);
			//保存全部
			session.pushAll(function(err) {
				cb();
			});
			session.on('closed', onUserLeave.bind(null, App));
		}
	];

	G.async.series(funAry, function(err, res){
	
		if(err){	
			next(null,{code:404});
			return;
		}

		//加入默认全局频道		
		App.rpc.chat.chatRemote.add(session,uid,session.frontendId,function(err){
            G.trace('加入默认全局频道:'+uid);
        });

		G.trace('登录 uid:'+uid);

		var rs = {
			"code" : 200
		}
		if(dataInfo['roomInfo']){
			rs['roomInfo']	= dataInfo['roomInfo'];

			if( Array.isArray(rs['roomInfo'].players) ){
	            rs['roomInfo'].players = {};
	        }
	        if( Array.isArray(rs['roomInfo'].upPlayers) ){
	            rs['roomInfo'].upPlayers = {};
	        }

			G.updateSession(session,{
                "roomId"    : rs['roomInfo'].roomId,
                "roomType"  : 'game_'+rs['roomInfo'].game
            });
		}

		next(null,rs);
	});

};


//session close 处理
var onUserLeave = function(app, session) {

	if(!session || !session.uid) {
		return;
	}

	var uid = session.uid;

	//退出默认全局频道
	App.rpc.chat.chatRemote.leave(session,uid,session.frontendId,function(err){
        G.trace('退出默认全局频道:'+uid);
    });
	
	G.trace('登出 uid:'+uid);

	var kickFlag = C.kickUids[uid] || 0;//踢出标志

	G.trace('[kickFlag]: '+kickFlag+' uid='+uid);

	//掉线退出房间
	//roomType:game_dzPoker game_cowWater
	function outRoomByGame(roomType,roomId){
		if(!roomType || !roomId){
			return;
		}
		//根据统一路由算法 算出指向的 游戏服务器id
		G.trace('roomType:'+roomType);
		G.trace('roomId:'+roomId);
		var connectors = App.getServersByType( roomType );
		
		var idx = Math.abs( Number(roomId) ) % connectors.length;

		var sid = connectors[idx].id;

		var msgData = {
			namespace : 'user',
			serverType:roomType,
			service : 'roomRemote',
			method : 'outRoom',
			args : [uid,roomId,kickFlag]
		};
		App.rpcInvoke(sid,msgData,function(rs){
			G.trace(sid+' 退出游戏房间 '+roomType);
			G.trace(rs);
		});
	}	

	var userInfo = session.get('userInfo');
	outRoomByGame( userInfo['roomType'] ,userInfo['roomId']);	

	//退出游戏房间 德州扑克
    // app.rpc.game_dzPoker.roomRemote.outRoom(session,uid,kickFlag, function(rs) {
    //     G.trace('德州扑克 退出游戏房间:'+rs);
    // });
 	//退出游戏房间 牛加水
    // app.rpc.game_cowWater.roomRemote.outRoom(session,uid,kickFlag, function(rs) {
    //     G.trace('牛加水 退出游戏房间:'+rs);
    // });


    if(kickFlag){
    	G.trace('delete C.kickUids[uid]');
    	delete(C.kickUids[uid]);
    }
};