/*
 * php消息推送定时器模块
 *
 * author cxf
 * versions 2017-07-13
 */

var Cron = module.exports;


/**
 * 定时消息推送 轮询PHP
 */
Cron.getPHPMsg = function() {

    //return;
	//G.trace('getPhpMsg');

	G.phpPost({
        "mod":"common",
        "op":"getMsg"
    },function(postRs){    
     
        if(!postRs){                            
            console.error('post php error');
            return false;
        }   
        
        if( !Array.isArray(postRs.data) ){
            console.error('postRs error');
            G.trace(postRs);
            return false;
        }

        if(postRs.data.length == 0 ){
            return false;
        }

        

        var channelObj = App.get('channelService');
        
        var uidArr;
        var rs;
        var msgData = postRs.data; 

        G.trace('有消息');
        G.trace(msgData);

        for(var i in msgData){
            G.trace('msgData i='+i);
            G.trace(msgData[i]);
      
            uidArr = msgData[i].uidArr ;
            rs = msgData[i].data;

            //如果id列表是空表示全服推送
            if( uidArr.length==0 ){
                //全服推送       
                App.rpc.chat.chatRemote.pushMessage(1,'onMsg',rs,function(err){
                    G.trace('全服推送结束');
                });
            }else{
                //指定uid数组推送
                for( var ui in uidArr){   
                    //不在线就不推送                                    
                    channelObj.pushMessageByUids('onMsg', rs, [{'uid':uidArr[ui], 'sid':G.frontendId}], function(){});
                }

            }

            
        }

        
    });  
};