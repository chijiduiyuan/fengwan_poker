/*
 * Remote模块 前端服务器 管理 游戏中房间列表
 *
 * author CXF
 * versions 2016-08-21
 */

var Remote = module.exports;
/**
 * 取房间所在服务器
 */
Remote.getRoom = function(roomId,gameType,cb) {

    G.trace('取房间所在服务器 gameType='+gameType);

    var sid=false;
    if(  C['roomByServer'][gameType][ roomId ] ){
        sid = C['roomByServer'][gameType][ roomId ];
    }
    G.trace('roomId='+roomId+' sid='+sid);
    G.trace(C['roomByServer']);

    cb(sid);
};


/**
 * 创建房间
 */
Remote.addRoom = function(roomIdArr,gameType,serverId,cb) {

    G.trace('创建房间gameType='+gameType+' serverId='+serverId);

    for(var i in roomIdArr){
        C['roomByServer'][gameType][ roomIdArr[i] ] = serverId;
    }
    G.trace(C['roomByServer']);

    cb();
};

/**
 * 删除房间
 */
Remote.delRoom = function(roomIdArr,gameType,cb) {

    G.trace('删除房间 gameType='+gameType);

    for(var i in roomIdArr){
        delete C['roomByServer'][gameType][ roomIdArr[i] ];
    }
    G.trace(C['roomByServer'])

    cb();
};
