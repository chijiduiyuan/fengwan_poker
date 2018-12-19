/*
 * Remote模块 德州扑克 处理掉线
 *
 * author CXF
 * versions 2016-08-21
 */

var Remote = module.exports;


/**
 * 退出房间 
 */
Remote.outRoom = function(uid,roomId,kickFlag,callback) {
    console.log('------------- 掉线处理 德州扑克'+ App.getServerId()+' ------------uid='+uid+'----roomId:'+roomId );
    callback('Remote.outRoom dzPoker uid='+uid+' kickFlag='+kickFlag);
    
    //重复登录导致的被t 不需要做退出操作
    if( !kickFlag ){
    	G.roomObj.standUpByUid(uid,roomId,function(rs){
           
            if(rs===200){
                 G.trace('德州扑克 断线站起 成功');                
            }

            if( rs === false ){
                G.trace('德州扑克 断线站起 失败 : 在游戏中且未弃牌 uid='+uid); 
            }else{               
                G.roomObj.outRoom(uid,function(){
                    G.trace('德州扑克 掉线退出房间 uid:'+uid);               
                });
            }
        	
        },'offline');
        
    }else{
        G.trace('德州扑克 重复登录导致的被t 不需要做退出操作');
    }

};


/**
 * 退出房间 
 */
Remote.publicInRoomSiteDown_remote = function(uid,roomInfo,callback) {
    G.trace('远程RPC publicInRoomSiteDown_remote uid='+uid+' roomInfo');

    callback('callback 远程一键坐下');
    
    G.roomObj.publicInRoomSiteDown_next(uid,roomInfo);
    

};