/*
 * Room_thriDucal
 * 三公 房间类
 * @author cjb
 * versions 2018-02-23
 */

var Game_thriDucal = require('./Game_thriDucal.class');
//房间操作类
var obj = new( require('./Room.class') );
obj.init('thriDucal',Game_thriDucal);

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


    //房间是否存在 是否已经开始游戏
    var roomInfo = this.getRoomByUID(uid);
    if( typeof(roomInfo)!='string' ){  //游戏已开始 结束后才能站起
        var curPlayer = roomInfo.players[uid];
        if(!curPlayer ){
            console.error('不在房间座位上 uid='+uid);            
            callback(404);
            return;
        }

        if(offline){
            curPlayer.offlineTimeout = 1;//掉线退出标志
            console.error('掉线退出标志 curPlayer.offlineTimeout=' + curPlayer.offlineTimeout);
            callback(false);    
            return;
        }

        G.trace('游戏过程中无法站起,等待游戏结束后站起');
        callback(404);    
        return;
       
    }

    var postPar = {
        "mod"   : "room",
        "game"  : "thriDucal",
        "op"    : "standUp",
        "uid"   : uid
    };
    let self = this;

    G.phpPost(postPar,function(postRs){   

        if(!postRs){     
            console.error('post php error standUpByUid');
            callback(404);
            return;
        }   
        if(postRs.code!=200){                        
            callback(postRs.code);
            return;
        }   
        //发送给座位上和旁观玩家,本人站起操作  
        self.toClient({
           "uid":uid,
           "op":"standUp"
        },postRs.data.uidArr); 

        callback(200);
    });  

}//standUpByUid


obj.outRoom = function(uid,callback){
    let self = this;
    //如果是在游戏中坐下且未弃牌的玩家无法退出房间
    G.phpPost({
        "mod"           :"room",
        "game"          :"thriDucal",
        "op":"outRoom",    
        "uid":uid    
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
            //如果战斗中则在战斗缓存中清除旁观列表中的该玩家信息
            delete(roomInfo.upPlayers[uid]);

            if(roomInfo.players[uid]){
                roomInfo.players[uid].standUp = 1;
                roomInfo.players[uid].timeout = 1;
            }
            self.setUserRoomId(uid,false);//删除玩家房间id缓存
        }
               
    });

}//outRoom



