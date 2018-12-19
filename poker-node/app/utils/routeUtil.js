/*
 * 路由策略
 *
 * author cxf
 * versions 2016-06-10
 */

var dispatcher = require('./dispatcher');

var obj = module.exports;

//非法访问
obj.invalidRoute =  function(session, msg, app, cb) {
    return;
}
//game_dzPoker-路由规则
obj.game_dzPoker = function(session, msg, app, cb) {

    var res = gameRouteBy(session,'dzPoker',app);
    if(res.error){
        cb(res.error);
        return;
    }
    cb(null, res.id);
};

//game_cowWater-路由规则
obj.game_cowWater = function(session, msg, app, cb) {
    var res = gameRouteBy(session,'cowWater',app);
    if(res.error){
        cb(res.error);
        return;
    }
    cb(null, res.id);
};

obj.game_thriDucal = function(session, msg, app, cb) {
    var res = gameRouteBy(session,'thriDucal',app);
    if(res.error){
        cb(res.error);
        return;
    }
    cb(null, res.id);
};

obj.game_cowcow = function(session, msg, app, cb) {
    var res = gameRouteBy(session,'cowcow',app);
    if(res.error){
        cb(res.error);
        return;
    }
    cb(null, res.id);
};



function gameRouteBy(session,gameType,app){

    var serverType = 'game_'+gameType;

    G.trace(serverType+'-路由规则 ' );

    G.trace(C['roomByServer'][gameType]);


    if(!session.uid){        
        return {error:new Error('未登录非法请求')};
    }
    var gameServers = app.getServersByType(serverType);
    if(!gameServers || gameServers.length === 0) {
        return {error:new Error('can not find game servers : '+serverType)};
    }

    //单人/多人　分配处理
    var idx = session.uid;
    var sessionInfo = session.get('userInfo');
    if( sessionInfo.roomType && sessionInfo.roomId!=0){
        //如果已经在房间里面则必须固定一个进程处理
        var roomId = sessionInfo.roomId;
        //如果游戏正在进行中则缓存中有保存 serverId
        var sid = C['roomByServer'][gameType][roomId];
        if( sid ){

            var serverIndex = false;
            for(var i in gameServers){        
                if( gameServers[i].id ==sid ){
                    serverIndex = i;
                    break;
                }
            }
            if(serverIndex!==false){//指向缓存中保存的 服务器
                G.trace('指向缓存中保存的 服务器='+sid);
                return gameServers[serverIndex];
            }
        }

        //根据room计算
        idx = Math.abs( roomId );
        G.trace('[路由]如果已经在房间里面则必须固定一个进程处理 roomId:'+idx);
    }else{
        G.trace('[路由]未进入房间则根据uid指定一个进程处理 uid:'+idx);
    }

    //var idx = session.roomId*1;
    //G.trace('idx'+idx);
    var res = dispatcher.dispatch(idx, gameServers);

    return res;
}