/**
 * game_Cowcow Room
 *
 * author cjb
 * versions 2018-02-26
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

    G.roomObj.standUpByUid(uid,roomId,function(rs){
           
        if(rs===200 || rs===227 ){//227=已在旁观列表中              
             G.roomObj.outRoom(uid,function(){
                G.trace('牛牛 退出房间 uid:'+uid);               
            });             
        }

        G.updateSession(session,{
                "roomId"    : 0,
                "roomType"  : ''
            });

        next(null, {"code": rs});
        
        
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
        G.trace('backRoom失败 游戏未进行中');
        next( null, {"code":200} );
        return ;
    }
    
    //更新倒计时误差
    var ts = G.time()-roomInfo.ts_time;
    if(ts<0){
        ts = 0;
    }

    
    var cardNum = 0;
    //根据游戏状态需要返回 已显示的牌

    // 牛牛默认一开始现实3张
    cardNum = 3;
 
    
    var otherPlayerCards = {};
    var playerBetList = {};
    
    for (var ui in roomInfo.players) {  
        //显示坐下玩家的最新筹码
        playerBetList[ui] = roomInfo.players[ui].bet;
        //显示其他坐下玩家的手牌
        if(ui==uid){
            continue;
        }
        //根据当前回合状态显示不同手牌数
        otherPlayerCards[ui] = [];
        for(var i=0;i<cardNum;i++){
            otherPlayerCards[ui].push( G.cardToNum(roomInfo.players[ui].cards[i],'cowWater') );
        }  
    }
    
    var rs = {
        "bankerSite" : roomInfo.bankerSite,//庄家座位
        "otherPlayerCards" : otherPlayerCards, //显示其他坐下玩家的手牌
        "playerBetList" : playerBetList,//显示坐下玩家的最新筹码
        //本人 牛牛叫庄叫庄倍率 -1表示没有叫过 0 表示没有资格 >0表示叫庄倍率
        "callBankerOdds_COW" : 0,

        "maxCallBankerOdds_Cow": roomInfo.maxCallBankerOdds_Cow,//牛牛最大叫庄倍率
        "curRound"    :roomInfo.curRound,//表示当前 游戏状态
        "ts"        : ts //当前轮倒计时
    };
   
    //坐下玩家的话要发自己的手牌
    if(roomInfo.players[uid]){
        rs['selfCards'] = G.cardToNum(roomInfo.players[uid]['cards'],'cowWater');
        //本人 牛牛叫庄叫庄倍率 -1表示没有叫过 0 表示没有资格 >0表示叫庄倍率
        rs['callBankerOdds_COW'] = roomInfo.players[uid].callBankerOdds_COW;
        //复位掉线标志
        roomInfo.players[uid].offlineTimeout = 0;
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
        next( null, {code:404} );
        return ;
    }

    //当前玩家    
    var curPlayer = roomInfo.players[uid];

    //玩家是否在游戏中
    if(!curPlayer){
        console.error('玩家不在房间位置上 uid='+uid);
        next( null, {code:404} );
        return;
    }
   
    if( ('step_'+msg.op)!= roomInfo.curRound ){
        console.error('操作过期 当前游戏状态不对 uid='+uid+' curRound='+roomInfo.curRound);
        console.error(msg);
        next( null, {code:404} );
        return;
    }


    switch(msg.op){        
    case 'callBanker_COW'://牛牛叫庄
        opInfo.odds = msg.odds*1;
        if(opInfo.odds == 0){//=0表示不叫庄
            break;
        }
        if(roomInfo.callBankerOdds_COW>opInfo.odds){
            console.error(msg.op+' 牛牛叫庄倍率 小与之前叫庄 uid='+uid);
            console.error(msg);
            next( null, {code:404} );
            return;
        }
        if( !G.inArray(opInfo.odds,CFG.game_cowcow.callBankerOdds_COW) ){
            console.error(msg.op+' 牛牛叫庄倍率 错误 uid='+uid);
            console.error(msg);
            next( null, {code:404} );
            return;
        }
        if(roomInfo.siteNum>5){//7人局筹码不足底注30倍不能参与叫庄
            if(curPlayer.bet< roomInfo.baseBet*30){
                console.error(msg.op+' 三公叫庄失败 7人局筹码不足底注30倍不能参与叫庄 uid='+uid);
                console.error(msg);
                next( null, {code:404} );
                return;
            }
        }else{//5人局筹码不足底注20倍不能参与叫庄
            if(curPlayer.bet< roomInfo.baseBet*20){
                console.error(msg.op+' 三公叫庄失败 5人局筹码不足底注20倍不能参与叫庄 uid='+uid);
                console.error(msg);
                next( null, {code:404} );
                return;
            }
        }
        
        break;  
    case 'putCard_COW'://牛牛摆牌        
        var cards = msg.cards;
        var cardsIsOk = true;

        if(!Array.isArray(cards) || cards.length!=5 ){
            cardsIsOk = false;   
        }else{
            //验证每个牌必须是正确的 1-52   
            var tmpCards = G.clone( curPlayer.cards );
            var checkedIndexArr = [];//检测是不是完全手牌 用
            for(var i in cards){
                if( G.isInt(cards[i]) && cards[i]>0 && cards[i]<53 ){                   
                    //每张牌必须是自己手上的牌 且不能重复
                    var checkOk = false;     
                    for(var j in tmpCards){
                        if( G.inArray(j,checkedIndexArr) ){//已经验证过的牌 则跳过                            
                            continue;
                        }
                        var ct = G.cardToNum(tmpCards[j],'cowWater');
                        G.trace(tmpCards[j].n);
                        G.trace(cards[i]+'=='+ct);
                        if( cards[i] ==  ct){//单牌验证ok
                            G.trace('单牌验证ok');
                            checkOk = true;
                            checkedIndexArr.push(j);
                            break;
                        }
                    }    
                    if(!checkOk){//提交的牌型不完全等于手牌 则是非法操作
                        G.trace('提交的牌型不完全等于手牌 则是非法操作');
                        cardsIsOk = false;
                        break;
                    }
                }else{
                    cardsIsOk = false;
                    break;
                }
            }
        }          
        if(!cardsIsOk){
            console.error(msg.op+' 牛牛整理牌 cards错误 不是有效数组 uid='+uid);
            console.error(msg);            
            next( null, {code:404} );
            return;
        }
        opInfo.cards = cards;
        opInfo.ok = msg.ok;
        break;
    default:
        next(null, {code:405});
        return;
        break;
    }   

    roomInfo.opArr.push(opInfo);

    G.trace('增加操作');
    G.trace(opInfo);
    next(null, {code:200});
}//gameOp
