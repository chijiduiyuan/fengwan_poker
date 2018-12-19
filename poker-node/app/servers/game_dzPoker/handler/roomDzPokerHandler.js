/**
 * game_dzPoker Room
 *
 * author cxf
 * versions 2017-06-10
 */

var Handler = module.exports;

/**
 * 公共大厅一键坐下
 */
Handler.publicInRoomSiteDown = G.roomObj.publicInRoomSiteDown;
/**
 * 进入房间
 */
Handler.in = G.roomObj.in;


/**
 * 退出房间
 */
Handler.out = function(msg, session, next) {

    var uid     = session.uid;
    var roomId = session.get('userInfo').roomId;

    G.trace('退出房间 UID='+uid);

    //是否在座位上 如果是则 必须先站起
     
    G.roomObj.standUpByUid(uid,roomId,function(rs){

        G.roomObj.outRoom(uid,function(rs){
            if(rs===true){
                //更新session
                G.updateSession(session,{
                    "roomId"    : 0,
                    "roomType"  : ''
                });
                code = 200;
            }else{
                code = 471;//OUT_ROOM_ERROR
            }

            G.trace('退出房间 结束:'+rs);        
            next(null, {"code": code});
        });
    });

   
}

/**
 * 坐下
 */
Handler.siteDown = G.roomObj.siteDown;

/**
 * 站起
 */
Handler.standUp = function(msg, session, next) {

    var uid     = session.uid;        
    var roomId = session.get('userInfo').roomId;

    G.roomObj.standUpByUid(uid,roomId,function(rs){
        G.trace('standUp 结果 code='+rs);
        next( null, {code:rs} );
    });
    return;
}

/**
 * 预约增加筹码
 */
Handler.preAddBet = G.roomObj.preAddBet;

/**
 * 开始游戏 代理开局
 */
Handler.startGame = G.roomObj.startGame;


/**
 * 断线重连请求 返回房间后的牌局信息
 */
Handler.backRoom=function(msg, session, next) {
    var uid     = session.uid;
     //房间是否存在
    var roomInfo = G.roomObj.getRoomByUID(uid);

    if( typeof(roomInfo)=='string' ){  //错误
        //console.error('不在房间中 根据id找房间没有找到 uid='+uid);
        G.trace('backRoom失败 游戏未进行中');
        next( null, {"code":200} );
        return ;
    }
    var rs = G.clone(roomInfo.process);
    //更新倒计时误差
    var ts = G.time()-rs.ts_time;
    if(ts<0){
        ts = 0;
    }
    rs.ts = ts;
    //坐下玩家的话要发自己的手牌
    if(roomInfo.players[uid]){
        rs['selfCards'] = G.cardToNum(roomInfo.players[uid]['cards'],'dzPoker');

        //复位掉线 超时标志
        roomInfo.players[uid].offlineTimeout = 0;
        //操作超时次数归零
        roomInfo.players[uid].opTimeoutNum = 0;
        
    }else{
        if(!roomInfo.upPlayers[uid]){
            roomInfo.upPlayers[uid] = {"uid":uid};
        }
    }    

    next( null, {"code":200,"gameProcess":rs} );
}//backRoom

/**
 * 游戏内操作
 */
Handler.gameOp = function(msg, session, next) {

    var uid     = session.uid;
    var opInfo  = {   
        uid : uid,
        op : msg.op,
        opid : msg.opid
    };


    G.trace('操作请求');
    G.trace(opInfo);

    //房间是否存在
    var roomInfo = G.roomObj.getRoomByUID(uid);

    if( typeof(roomInfo)=='string' ){  //错误
        console.error('不在房间中 根据id找房间没有找到 uid='+uid);
        next( null, {code:472} );//NOT_IN_ROOM
        return ;
    }

    //当前玩家    
    var curPlayer = roomInfo.players[uid];


    //玩家是否在游戏中，且未弃牌    
    if(!curPlayer){
        console.error('玩家不在房间位置上 uid='+uid);
        next( null, {code:473} );//NOT_IN_SITE
        return;
    }
    if(curPlayer && curPlayer.out){
        console.error('玩家已弃牌无法操作 uid='+uid);
        next( null, {code:474} );//IS_OUT_CARD
        return;
    }


    switch(msg.op){
    case 'addBet'://押注,加注
        //判断押注金额对不对 最大，最小值判断
        msg.bet *=1;
        opInfo['bet'] = msg.bet;

        if(msg.bet<0){
            next(null, {code:475});//押注金额必须大于0 BET_IS_ZERO
            return;
        }
        //押注金额超过剩余筹码
        if(msg.bet>curPlayer.bet){
            next(null, {code:476});//押注金额超过剩余筹码 BET_BIG_LAST
            return;
        }

        var maxGold = curPlayer.bet;

        var yzGold = 0;//最低押注额度
        if( !msg.bet || msg.bet <yzGold ){           
            next(null, {code:477});//押注金额不对 BET_ERROR
            return;
        }
        break;
    case 'followBet': //跟注
        break;
    case 'pass'://过牌
        break;
    case 'allin'://all in 
        break;
    case 'out'://弃牌
        break;
    default:
        next(null, {code:478});//未知操作 UNKNOW_OP
        return;
        break;
    }   

    roomInfo.opArr.push(opInfo);

    G.trace('增加操作');
    G.trace(opInfo);
    next(null, {code:200});
}//gameOp



/**
 * 公开自己的底牌
 */
Handler.openHandCard=function(msg, session, next) {
    var uid     = session.uid;
    //var roomId  = session.get('userInfo').roomId;
     //房间是否存在
    var roomInfo = G.roomObj.getRoomByUID(uid);
    var cardIndex = msg.cardIndex*1;
    var card = msg.card*1;
    if( typeof(roomInfo)!=='string' ){  //错误
        
        G.trace('游戏正在进行中，无法翻自己牌给别人看');
        next( null, {"code":200} );
        return ;
    }


    G.phpPost({
        "mod"   : "room",
        "game"  : "dzPoker",//dzPoker德州  cowWater牛加水
        "op"    : "getRoom",
        "uid"   : uid
    },function(postRs){   
        if(postRs.code!=200 || !postRs.data.roomInfo){
            G.trace('操作失败，房间没有找到');
            return;
        }
        var roomInfo = postRs.data.roomInfo;
        var uidArr = [];
        for(var i in roomInfo.players){
            //本人不需要发送 
            if(i==uid){
                continue;
            }
            uidArr.push(i);
        }
        //旁观者也要发送消息
        for(var j in roomInfo.upPlayers){
            //离开的玩家不需要发送 
            uidArr.push(j);
        }
       
        G.roomObj.toClient({
            "mod"       :"game_dzPoker",
            "op"        : "openHandCard", 
            "openUid"   : uid,//显示自己手牌的玩家
            "cardIndex" : cardIndex,//显示第几张牌
            "card"      : card//客户端单牌 1-52
                
        },uidArr);

    });



   

    next( null, {"code":200} );
}//openHandCard