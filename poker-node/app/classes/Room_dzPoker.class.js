/*
 * Room_dzPoker
 * 德州扑克房间类
 * @author cxf
 * versions 2017-08-25
 */

var Game_dzPoker = require('./Game_dzPoker.class');
//房间操作类
var obj = new( require('./Room.class') );
obj.init('dzPoker',Game_dzPoker);

module.exports = obj;

/***
 *   指定uid玩家位置上站起
 *
 */
obj.standUpByUid = function(uid,roomId,callback,offline){

    if( C['roomPlayRequesting'][roomId]  //已经开始请求php游戏开始了 正在等待结果中
        && (G.time()-C['roomPlayRequesting'][roomId]<10)  //10秒 超过时间表示异常 解除锁
    ){
        //游戏正在启动 请求php验证中
        console.error('游戏正在启动 请求php验证中 uid='+uid+' roomId='+roomId);            
        callback(404);
        return false;
    }


    var postPar = {      
        "mod"   : "room",
        "game"  : "dzPoker",
        "op"    : "standUp",
        "uid"   : uid
    };
    //房间是否存在 是否已经开始游戏
    var roomInfo = this.getRoomByUID(uid);
    if( typeof(roomInfo)!='string' ){  //游戏已开始 则弃牌后才能站起
        
        //当前玩家    
        var curPlayer = roomInfo.players[uid];
        if(!curPlayer || curPlayer.standUp){
            console.error('不在房间座位上 uid='+uid);            
            callback(404);
            return;
        }
        
        if(curPlayer.out){
            //游戏中但是已经弃牌 玩家从座位中站起,且不能影响到游戏过程            
            curPlayer.standUp = 1;

        }else if(offline){//掉线触发的站起
             //如果是在游戏中则未弃牌不能站起  掉线退出的时候 需要等到结算自动站起     
            console.error('游戏中则未弃牌不能站起 uid='+uid);          

            curPlayer.offlineTimeout = 1;//标志掉线超时
            curPlayer.opTimeoutNum = 0;//清空在线超时次数
            callback(false);
            return;
        }else{
            //手动直接退出/站起 可以立马弃牌+站起
            G.trace('手动直接退出/站起 可以立马弃牌+站起');
            curPlayer.standUp = 1;
            curPlayer.out = 1;
        }

        //可以站起
        roomInfo.upPlayers[uid] = G.clone(curPlayer);
        //如果是游戏中,弃牌后,站起成功,则发送PHP需要更新的玩家信息 因为筹码已经使用过
        postPar['playerInfo'] = roomInfo.upPlayers[uid];
    }
    let self = this;
    G.phpPost(postPar,function(postRs){   

        if(!postRs){     
            console.error('post php error standUpByUid');
            callback(404);
            return;
        }   
        if(postRs.code!=200){    

            G.trace('站起操作失败 code:'+postRs.code);

            callback(postRs.code);
            return;
        }   
        //发送给座位上和旁观玩家,本人站起操作  
        self.toClient({
           "uid":uid,
           "op":"standUp"
        },postRs.data.uidArr); 

        G.trace('发送给座位上和旁观玩家,本人站起操作');

        callback(200);
    });  

}//standUpByUid

//退出房间
obj.outRoom = function (uid,callback){
    let self = this;
    //如果是在游戏中坐下且未弃牌的玩家无法退出房间
    G.phpPost({
        "mod"   : "room",
        "game"  : "dzPoker",
        "op"    : "outRoom",    
        "uid"   : uid    
    },function(postRs){ 
        if(!postRs){                        
            console.error('post php error');
            callback(404);
            return;
        }   
        if(postRs.code!=200){        
            callback(postRs.code);
            return;
        }   
        callback(true);    

        //告诉房间内玩家信息             
        self.toClient({
           "uid":uid,
           "op":"outRoom"
        },postRs.data.uidArr);            

        //必须是旁观列表的玩家才能退出游戏
        var roomInfo = self.getRoomByUID(uid);
        if( typeof(roomInfo)!='string' ){     
            // //如果是坐下游戏中玩家 则弃牌并站起 然后退出房间 TODO
            // if(roomInfo.players[uid] && !roomInfo.players[uid].standUp){
            //     standUpByUid(uid,function(rs){
            //         delete(roomInfo.upPlayers[uid]);
            //     });
            // }

            //如果战斗中则在战斗缓存中清除旁观列表中的该玩家信息
            delete(roomInfo.upPlayers[uid]);
            self.setUserRoomId(uid,false);//删除玩家房间id缓存

        }
               
    });

}//outRoom