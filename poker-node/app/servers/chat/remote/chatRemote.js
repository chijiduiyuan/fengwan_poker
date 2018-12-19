/*
*   维护频道信息 PRC调用
*/

module.exports = function(app) {
    return new ChatRemote(app);
};

var ChatRemote = function(app) {
    this.app = app;
    this.channelService = App.get('channelService');
   
	this._name = 'all';

    this.channel = this.channelService.createChannel(this._name);
};

/**
 *  上线通知
 */
ChatRemote.prototype.add = function(uid, sid, cb) {
    this.channel.add(uid, sid);

    cb();
};

/**
*  channel清除下线的用户(通知还在线的玩家)
*/
ChatRemote.prototype.leave = function(uid, sid,cb) {
    this.channel.leave(uid, sid);
    cb();
};


/*
*   向所有在线用户推送消息
*/
ChatRemote.prototype.pushMessage = function(route,param,cb){

	// param = {"data":JSON.stringify(param)};
 //    G.trace(param);
    this.channel.pushMessage(route,param,cb);
}


/*
*  判断用户是否在线
*/
ChatRemote.prototype.isOnline = function(uid) {
    
    var tuid = this.channel.getMember(uid);
    if(tuid){
        return tuid;
    }else{
        return false;
    }
}

