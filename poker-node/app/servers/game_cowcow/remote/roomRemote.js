/*
 * Remote模块 牛牛 掉线处理
 *
 * author CXF
 * versions 2016-08-21
 */

var Remote = module.exports;


/**
 * 退出房间
 */
Remote.outRoom = function(uid,roomId,kickFlag,callback) {
    console.log('------------- 掉线处理 牛牛'+ App.getServerId()+' ------------uid='+uid+'----roomId:'+roomId );


    callback('Remote.outRoom cowcow uid='+uid+' kickFlag='+kickFlag+' sid='+App.getServerId() );
    

    //重复登录导致的被t 不需要做退出操作
    if( !kickFlag ){
        G.roomObj.standUpByUid(uid,roomId,function(rs){
           
            if(rs===200 || rs===227){//227=已在旁观列表中
                G.trace('牛牛 断线站起 成功');                
                G.roomObj.outRoom(uid,function(){
                    G.trace('牛牛 掉线退出房间 uid:'+uid);               
                });
            }
            
        },'offline');
        
    }else{
        G.trace('牛牛 重复登录导致的被t 不需要做退出操作');
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