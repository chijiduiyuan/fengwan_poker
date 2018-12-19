/**********************
 **    牛牛-游戏类
 **    @author cxf
 **    @since 2017-07-04
 *************************/

module.exports = function () {
    var self = this;

    this.cardArr        = []; //牌组类型
    this.players        = {}; //玩家列表
    this.upPlayers      = {}; //旁观列表
  
    this.curPlayerUID   = 0; //当前操作玩家

    this.timer = {
        time: 0,
        ts  :0,
        uid :0
    };
    // this.timeoutRound   = 22; //出牌超时时间 秒    
    // this.timeoutRoundClient   = 20; //出牌超时时间 秒 -客户端倒计时
    this.opUpdateTime = 5; //帧间隔

    this.opUpdateCheckTime = 200; //帧校验间隔

    this.opUpdateCheckMillisecond = 0; // 上次校验时间
  
    this.allPlayerHandCards = {}; //所有玩家手牌
 
    //牛牛牌型 大小
    this.cType_COW      = {
        "COW_FIVE_SMALL"    :17, //五小牛
        "COW_BOMB"          :16, //炸弹
        "COW_GOLD"          :15, // 金牛
        "COW_SILVER"        :14, // 银牛
        "COW_BIG_BULL"      :13, //牛牛 有牛+10点
        "COW_BULL_9"        :12, //牛9 有牛+点
        "COW_BULL_8"        :11, //牛8
        "COW_BULL_7"        :10,
        "COW_BULL_6"        :9,
        "COW_BULL_5"        :8,
        "COW_BULL_4"        :7,
        "COW_BULL_3"        :6,
        "COW_BULL_2"        :5,
        "COW_BULL_1"        :4,  
        "COW_NO_BULL"       :3 //没牛        
    };
    this.baseBet_COW    = 0; //牛牛基础底注
    
    this.bankerUid_COW = 0; //牛牛 庄家 uid4

    this.maxCallBankerUids_COW  = []; //牛牛最大叫庄倍率 玩家数组

    this.over = false; //游戏结束标志

    this.roomId         = null; // 房间Id
    this.roomInfo       = {}; // 房间信息

    this.seqNum = 0; //服务端消息发送客户端递增序号

    //callBankerStep_COW 牛牛叫庄
    //putCardStep_COW 牛牛搓牌 + 摆牌
    

    /**
     * 初始化玩家信息
     * @return void
     * @access public
     */
    this.init = function(roomData){      
        C.seqNum++; // 累加游戏实例数目
        this.seqNum = C.seqNum*100;

        G.trace('[init] game_cowcow roomId：'+this.roomId+' seqNum='+this.seqNum);
        G.trace(roomData);
        
        //房间状态改为游戏中
        roomData.status = 1; //status 状态 1游戏中
     
        this.roomInfo   = roomData; //房间信息
        this.roomId     = this.roomInfo.roomId;
        this.players    = this.roomInfo.players;
        this.upPlayers  = this.roomInfo.upPlayers;

        this.baseBet_COW = this.roomInfo.baseBet*1; //底注

        this.roomInfo.maxCallBankerOdds_COW = 0; //牛牛最大叫庄倍率

        this.roomInfo.curRound = 'step_ready';//游戏状态 准备开始
        this.roomInfo.ts_time = 0;
     
        //庄家位置 
        this.roomInfo.bankerSite = -1;

        //初始化牌局玩家信息
        var playerInfo  = this.roomInfo.players;
        this.roomInfo.site = [];
        //座位信息
        for(var si=0;si<this.roomInfo.siteNum;si++){
            this.roomInfo.site.push(0);
        }       
        
        for(var i in playerInfo){
            this.players[i] = {
                "uid"       : playerInfo[i].uid,      
                "bet"       : playerInfo[i].bet*1,      
                "point"     :0,
                "cards"     :{},
                "site"      :playerInfo[i].site*1,

                "callBankerOdds_COW" : -1,//牛牛叫庄倍率
                "isPutCard_COW" : false,//牛牛已摆牌                                
                "winBet_COW"    : 0,//牛牛赢的筹码
                "loseBet_COW"   : 0,//牛牛输的筹码
                "offlineTimeout" : 0 //掉线超时 结算后退出房间
         
            }         
            //座位列表 整理
            this.roomInfo.site[playerInfo[i].site] = i;
        }        
        
        //生成52张牌 除了大小鬼
        this.createCard();     
      

        this.toClient({
            "op"    :"nn_start"            
        });
        G.trace('暂停2秒 开始 牛牛 叫庄');

        
        setTimeout(function(){           
            self.nextRound();
            self.doUpdate();            
        }, 1000);

        return true;
    };//init


    /***
     * 每帧执行
     * @return
     */

    this.doUpdate = function(){

        //读取玩家操作请求 
        var opArr = this.roomInfo.opArr;
        var opInfo,opOk;
        while(opArr.length>0){
            if(this.over){
                break;
            }
            opOk = false;
            opInfo = opArr.shift();              
            
            G.trace('开始处理玩家操作请求');
            G.trace(opInfo);           

            if( 'step_'+opInfo.op!= this.roomInfo.curRound ){
                console.error('非法失败 当前游戏状态不对 uid='+opInfo.uid+' curRound'+roomInfo.curRound);
                this.toClient({
                    "op"    : 'opError',
                    "opid"  : opInfo.opid,     
                    "msg"   : 'COW_TURN_ERROR'
                },opInfo.uid);
                continue;            
            }


            switch(opInfo.op){           
            case 'callBanker_COW'://牛牛叫庄            
                this.callBankerOdds_COW(opInfo.uid,opInfo.odds);
                break;  
            case 'putCard_COW'://牛牛摆牌          
                this.putCard_COW(opInfo.uid,opInfo.cards,opInfo.ok);                
                break;
            default:
                console.error('未知操作 op='+opInfo.op);
                break;
            }
           //游戏结束退出循环
            if(this.over){
                return ;
            }
        }

        var millisecond = G.millisecond();

        if (this.opUpdateCheckMillisecond + this.opUpdateCheckTime < millisecond) {

            if( this.over ){
                G.trace('[OVER]');
                return;
            }
            //有定时器
            if( !this.timer.stop ){
                var now     = G.time();
                if(now > this.timer.time){
                    // var curTs   = this.timer.ts - (now-this.timer.time);
                    // this.timer.time = now;
                    // this.timer.ts   = curTs;               
                    // if( curTs<= 0 ){ //定时器时间到 执行定时事件
                        G.trace('[定时处理]');
                        this.delTimer();
                        this.timer.fun.apply(this);
                        //游戏结束退出函数
                        if(this.over){
                            return ;
                        }
                    //}
                }
            }else{
                G.trace('[no Time]');
            }

            this.opUpdateCheckMillisecond = millisecond;
        }

        //游戏结束 或 进入下一个轮询
        if(this.over){
            return ;
        }else{
            setTimeout(function(){
                self.doUpdate();
            }, this.opUpdateTime);
        }
    }//doUpdate

    /**
     * 进入下一阶段
     * @return
     * @access public
     */
    this.nextRound = function(){
        //清除定时函数 取消未执行的定时函数
        
        this.delTimer();        
 
        switch(this.roomInfo.curRound ){              
        case 'step_ready' ://游戏开始           
            //进入牛牛叫庄
            G.trace('进入 牛牛叫庄');
            this.roomInfo.curRound = 'step_callBanker_COW';       
            this.roomInfo.ts_time = G.time()+CFG.game_cowcow.callBankerTimeout_COW;    
            this.setTimer( this.nextRound,CFG.game_cowcow.callBankerTimeout_COW );   
            break;
        case 'step_callBanker_COW':  
            //处理牛牛叫庄结果
            G.trace('处理牛牛叫庄结果');
            if(this.roomInfo.maxCallBankerOdds_COW == 0 ){//没人叫庄则 在允许叫庄的玩家列表中随机            
                this.roomInfo.maxCallBankerOdds_COW = 1;         
                for(var i in this.players){
                    this.maxCallBankerUids_COW.push(i);
                }
            }
            //最大叫庄玩家列表里面随机取一个
            this.bankerUid_COW = G.arrayRand(this.maxCallBankerUids_COW);       
            //庄家位置更新 
            this.roomInfo.bankerSite = this.players[this.bankerUid_COW].site;    
            //发送牛牛叫庄结果给客户端
            this.toClient({
                "op"    :"cow_callBanker",
                "uid"   :this.bankerUid_COW, //牛牛庄家
                "maxCallBankerUids_COW" : this.maxCallBankerUids_COW,//最大叫庄玩家列表
                "odds"  :this.roomInfo.maxCallBankerOdds_COW //叫庄倍率
            });

            //牛牛 发牌
            this.sendCard_COW();
            G.trace('牛牛 发牌 结束 ');

            //进入 牛牛摆牌
            G.trace('进入 牛牛搓牌 摆牌');
            this.roomInfo.curRound = 'step_putCard_COW';
            //设置超时进入下一轮
            this.roomInfo.ts_time = G.time()+CFG.game_cowcow.putCardTimeout_COW;    
            this.setTimer( this.nextRound,CFG.game_cowcow.putCardTimeout_COW  );  //后台增加2秒用做网络延迟
            break;
        case 'step_putCard_COW':
            //牛牛摆牌结束 开始结算
            
            G.trace('进入（牛牛checkout）');
            this.roomInfo.curRound = 'step_checkout_COW';            
            this.checkout_COW();
            return;//牛牛结算后 结束游戏
            break;
        default: 
            //错误
            console.error('状态机错误 未知'+this.roomInfo.curRound);
            return;
            break;
        }

        this.toClient({
            "op" : "nextRound",
            "round" : this.roomInfo.curRound
        });

    }//nextRound

    /**
     * 设置定时器
     * @param {string} callback 回调函数名
     * @param {int} ts 剩余时间 单位秒
     * @param {int} uid
     * @return
     * @access public
     */
    this.setTimer = function(callback,ts,uid){
       // ts = ts||this.timeoutRound;
       // ts *= 1000;//时间戳
        this.timer = {         
            time: G.time()+ts,
            ts  :ts,
            uid :uid||this.curPlayerUID,
            fun :callback,
            stop:0
        };
        G.trace('setTimer');
        G.trace(this.timer);
    }//setTimer

    /**
     * 删除定时器
     * @param {string} name 定时器名称,为空则删除所有定时器
     * @return void
     * @access public
     */
    this.delTimer = function(name){
        G.trace('delTimer');
        this.timer.stop = 1;
    }//delTimer

    /**
     * 生成牌
     * @return void
     * @access public
     */
    this.createCard = function(){
        this.cardArr = [];
        //创建52个除大鬼小鬼外的牌
        //2 0 3 1 大小顺序
        
        for (var i=0; i<4; ++i){  //0=redHeart ,1=redBlock, 2=blackHeart,3=blackGrass
            for (var j=1; j<14; ++j){
                this.cardArr.push({
                    "c" : i,
                    "n" : j
                });
            }
        }
        this.washCard();
    }//createCard

    /**
     * 洗牌
     * @return void
     * @access public
     */
    this.washCard = function(){

        // var one =  [
        //                {
        //                   "c" : 0 ,
        //                   "n" : 4 
        //                },
        //                {
        //                   "c" : 1 ,
        //                   "n" : 2 
        //                },
        //                {
        //                   "c" : 2 ,
        //                   "n" : 11 
        //                },
        //                {
        //                   "c" : 0,
        //                   "n" : 2 
        //                },

        //                 {
        //                   "c" : 1,
        //                   "n" : 7 
        //                },
        //             ];
        // var two = [
        //                {
        //                   "c" : 1 ,
        //                   "n" : 5 
        //                },
        //                {
        //                   "c" : 2 ,
        //                   "n" : 3 
        //                },
        //                {
        //                   "c" : 3 ,
        //                   "n" : 12 
        //                },
        //                {
        //                   "c" : 3,
        //                   "n" : 3 
        //                },

        //                 {
        //                   "c" : 2,
        //                   "n" : 6 
        //                },
        //         ];

        
        G.randomAry( this.cardArr );
        // this.cardArr.unshift(one[0],one[1],two[0],two[1],one[2],two[2],one[3],one[4],two[3],two[4]);

    }//washCard


    
   /**
     * 牛牛发牌
     * @return void     
     * @access public
     */    
    this.sendCard_COW = function(){
        var tmpCard,tmpCardArr,cardKey;
        //每个人发5张
        var preNum = 5;        
        G.trace('牛牛发牌 每个人发5张牌'); 

        for(var p in this.players){
            this.players[p]['cards'] = [];      
            tmpCardArr =[];

             //发最后2张底牌
            for(var i=0;i<preNum;i++){
                tmpCard = this.cardArr.shift();
                tmpCardArr.push(tmpCard);                
                this.players[p]['cards'].push(tmpCard);                
            }          
            //底牌只能看自己的
            this.toClient({
                "op"        :'cow_self_showCards',                    
                "cards"     :G.cardToNum(tmpCardArr,'cowWater')
            } ,this.players[p]['uid'] );
        }        
    }//sendCard_COW

    
     
   
    
    /**
     * 牛牛叫庄
     * @param {int} uid
     * @param {int} odds
     * @return void     
     * @access public
     */ 
    this.callBankerOdds_COW = function(uid,odds){

        this.players[uid].callBankerOdds_COW  = odds;
     
        //保存最大叫庄倍率和玩家
        if(odds > this.roomInfo.maxCallBankerOdds_COW){
            //更新最大叫庄
            this.roomInfo.maxCallBankerOdds_COW = odds;
            this.maxCallBankerUids_COW = [uid];
        }else if(odds == this.roomInfo.maxCallBankerOdds_COW){
            //同样是最大叫庄 增加玩家
            this.maxCallBankerUids_COW.push(uid);
        }

        //发送给客户端表示叫庄情况
        // this.toClient({
        //     "op"   : 'cow_callBanker',
        //     "uid"  : uid,
        //     "odds" : odds
        // });
         //如果所有人都已经叫庄过了则进入下一轮
        var allDone = true;
        for(var i in this.players){
            if(this.players[i].callBankerOdds_COW===-1){
                allDone = false;
                break;
            } 
        }
        if(allDone){
            this.nextRound();
        }
    }//callBankerOdds_COW
    


     /**
     * 牛牛摆牌
     * @param {int} uid
     * @param {array} cards 卡牌数组 1-52
     * @return void     
     * @access public
     */ 
    this.putCard_COW = function(uid,cardNumArr,ok){

        var cards = [];

        for(var n in cardNumArr){            
            cards.push( CFG.card.cowWater_numToObj[cardNumArr[n]] );
        }

        this.players[uid].cards = cards;

        if(!ok){//摆牌未结束
            return;
        }

        this.players[uid].isPutCard_COW = true;
        //如果所有人都已经摆牌过了则进入牛牛结算
        var allOk = true;
        for(var i in this.players){
            if(!this.players[i].isPutCard_COW){
                allOk = false;
                break;
            }
        }

        //this.nextRound();
        if(allOk){
            //超时进入牛牛结算
            this.nextRound();            
        }else{
            //发送给自己 牌型 客户端要提前展示自己用
            var rs = this.getCardType_COW(uid);
            this.toClient({
                "op"   : 'cow_selfCardType',
                "type" : rs.type, //牌型
                "typePoint" : rs.typePoint//默认为0 如果是牛几 则是点数
            },uid);
        }
    }//putCard_COW
    


  

    /**
     * 取牌类型 牛牛
     * @param {uid} 玩家uid
     * @return  {array} 牌型信息
     * @access public
     * 
     */
    this.getCardType_COW = function(uid){
    
        G.trace('[getCardType_COW] 取牌型 大小 uid='+uid);
        var cards = [];
        var maxN = 0;
        var maxC = -1;
        var tempC = -1
        for(var ci=0;ci<5;ci++){ //牛牛比牌要全部5张
            if (maxN < this.players[uid]['cards'][ci]['n']) {
                maxN = this.players[uid]['cards'][ci]['n'];
                maxC = this.pokerSuit(this.players[uid]['cards'][ci]['c']);
            }else if(maxN == this.players[uid]['cards'][ci]['n']){
                tempC =  this.pokerSuit(this.players[uid]['cards'][ci]['c']);
                if(tempC< maxC) {
                    maxC = tempC;
                }
            }
            cards.push(this.players[uid]['cards'][ci]);            
        }
        // var extraPoint = maxN * 1000 + maxC * 100;
        G.trace('[getCardType_COW] 手牌 uid='+uid);
        G.trace('maxN = '+maxN+ " maxC = "+ maxC);
        G.trace(cards);        

        if( this.is5SmallCow(cards) ){
            G.trace('五小牛');
            //主牌型评分 五公没有在比大小
            var tmpNum = this.cType_COW.COW_FIVE_SMALL *1000000; // 五小牛 庄吃           
            return {
                "type"      : "COW_FIVE_SMALL",                
                "typePoint" : 0,                
                "point"     : tmpNum,
                "cards"     : cards
            };
        }

        if(this.isBomb(cards)>0){    

             G.trace('炸弹');
            //主牌型评分 五公没有在比大小
            var tmpNum = this.cType_COW.COW_BOMB *1000000 + this.isBomb(cards) * 1000; //炸弹 比炸弹大小            
            return {
                "type"      : "COW_BOMB",                
                "typePoint" : 0,                
                "point"     : tmpNum,
                "cards"     : cards
            };
        }



        if ( this.isGoldCow(cards)) {
             G.trace('金牛');
            //主牌型评分 五公没有在比大小
            var tmpNum = this.cType_COW.COW_GOLD *1000000 + maxN * 1000 + maxC * 100;  //金牛 比花色比大小          
            return {
                "type"      : "COW_GOLD",                
                "typePoint" : 0,                
                "point"     : tmpNum,
                "cards"     : cards
            };

        }

        if (this.isSilverCow(cards)) {
            G.trace('银牛');
             var tmpNum = this.cType_COW.COW_SILVER *1000000 + maxN * 1000 + maxC * 100;  //银牛 比花色比大小          
            return {
                "type"      : "COW_SILVER",                
                "typePoint" : 0,                
                "point"     : tmpNum,
                "cards"     : cards
            };
        }

        //是否有牛 前3张总和是10的倍数                   
        var hasCowPoint = this.cowCount([cards[0],cards[1],cards[2]]);      

        if(hasCowPoint>0){//没有牛
            G.trace('没牛');
            //主牌型评分 没牛 没有在比大小
            var tmpNum = this.cType_COW.COW_NO_BULL *1000000 + maxN * 1000 + maxC * 100; //没牛 比花色比大小           
            return {
                "type"      : "COW_NO_BULL",     
                "typePoint" : 0,                           
                "point"     : tmpNum,
                "cards"     : cards
            };
        }

        // 有牛+1对 
        // if( cards[3].n==cards[4].n ){
        //     //牛宝宝+对A
        //     if(cards[3].n==1){
        //         G.trace('牛宝宝+对A');  
        //         var tmpNum = this.cType_COW.COW_BABY_TOW_CARD *1000000
        //                     + cards[3].n*100;            
        //         return {
        //             "type"      : "COW_BABY_TOW_CARD",                
        //             "typePoint" : 0,                
        //             "point"     : tmpNum,
        //             "cards"     : cards
        //         };
        //     }

        //     //牛宝宝
        //     G.trace('牛宝宝 有牛+1对');           
        //     var tmpNum = this.cType_COW.COW_BABY_CARD *1000000
        //                 + cards[3].n*100;            
        //     return {
        //         "type"      : "COW_BABY_CARD",       
        //         "typePoint" : 0,                         
        //         "point"     : tmpNum,
        //         "cards"     : cards
        //     };
        // }

        //牛冬菇 有牛+黑桃A+J/Q/K
        // if( (cards[3].c==2 && cards[3].n==1) 
        //     || cards[4].c==2 && cards[4].n==1)
        // {//有黑桃A
        //     if( 
        //         (cards[3].n==11 ||cards[3].n==12||cards[3].n==13 ) ||
        //         (cards[4].n==11 ||cards[4].n==12||cards[4].n==13 )
        //     ){//另外一张是 JQK
        //         G.trace('牛冬菇 ');
        //         //主牌型评分
        //         var tmpNum = this.cType_COW.COW_NDG_CARD *1000000;                                      
        //         return {
        //             "type"      : "COW_NDG_CARD",     
        //             "typePoint" : 0,                           
        //             "point"     : tmpNum,
        //             "cards"     : cards
        //         };
        //     }
        // }

            
        var cowPoint = this.cowCount( [ cards[3],cards[4] ] ); 

        //牛牛
        if(cowPoint==0){
            G.trace('牛牛');
            
            //主牌型评分
            var tmpNum = this.cType_COW.COW_BIG_BULL *1000000 + maxN * 1000 + maxC * 100; //牛牛 比花色比大小                                      
            return {
                "type"      : "COW_BIG_BULL",   
                "typePoint" : 0,                             
                "point"     : tmpNum,
                "cards"     : cards
            };
        }
    
        //牛几
        G.trace('牛几:'+cowPoint);
        var tempCardType = "COW_BULL_"+cowPoint;
        var tmpNum = this.cType_COW[tempCardType] *1000000 + cowPoint * 100; // 有牛，比点数      
        return {
            "type"      : tempCardType,   
            "typePoint" : cowPoint,                         
            "point"     : tmpNum,
            "cards"     : cards
        };
    }//getCardType_COW
    

    /**
     * 获取花色大小.
     * @param  {number} color 花色
     * @return {number}       花色编码
     */
    this.pokerSuit = function(color) {
        var num;
        switch (color) {
            case 2:
                num = 3;       
                break;

            case 0:
                num = 2;
                break;

            case 3:
                num = 1;
                break;
            case 1:
                num = 0;
                break;
            default:
                num = -1;
                break;
        }
        return num;
    }//pokerSuit

    /**
     * 牌组是否是JQK   
     * @param {array} 牌型信息          
     * @return  {bool}           
     */
    this.isJQK = function(cards){
        var rs = true;
        var n;
        for(var i in cards){
            n = cards[i].n;
            if( n==11 || n==12 || n==13){
                continue;
            }else{
                rs = false;
                break;
            }   
        }                   
        return rs;        
    }//isJQK

    /**
     * 是否5小牛.
     * @param  {array} 牌组.
     * @return {bool} 结果.
     */
    this.is5SmallCow = function(cards) {
        var rs = true;
        var n;
        var total = 0;
        for (var i  in cards) {
            n = cards[i].n;            
            if (n > 4) { 
                rs = false;
                break;
            } 
            total += n;                         
        }        
        if (rs && total > 10) {
            rs = false;
        }
        return rs;
    }//is5SmallCow

    /**
     * 是否炸弹.
     * @param  {array} 牌数组.
     * @return {bool} 结果.
     */
    this.isBomb = function(cards) {
        var rs = 0;
        var temp = {};
        for (var i  in cards) {
            if (!temp[cards[i].n]) {
                temp[cards[i].n] = 1;
            }else{
                temp[cards[i].n] = temp[cards[i].n] + 1;
            }
        }
        for(var j in temp){
            if (temp[j] && temp[j] === 4) {
                rs = j;
                break;
            }
        }
        return rs;
    }//isBomb


    /**
     * 是否金牛.
     * @param  {array} 牌型信息.
     * @return {bool} 结果.
     */
    this.isGoldCow = function(cards){
        var rs = true;
        var n;
        for(var i in cards){
            n = cards[i].n;
            if( n==11 || n==12 || n==13){
                continue;
            }else{
                rs = false;
                break;
            }   
        }                   
        return rs;        
    }//isGoldCow

    /**
     * 是否银牛.
     * @param  {array} 牌型信息.
     * @return {bool} 结果.
     */
    this.isSilverCow = function(cards){
        var rs = true;
        var n;
        for (var i  in cards) {
            n = cards[i].n;
            if (n == 10 ||n == 11 || n == 12 || n == 13) {
                continue;
            }else {
                rs = false;
                break;
            }
        }
        return rs;
    }//isSilverCow


    
    /**
     * 点数计算是否有牛 
     * @param {array} 牌型信息          
     * @return  {int}  0表示有牛 >0 表示没牛点数            
     */
    this.cowCount =function(cards){
        var rs = true;
        var n=0;
        for(var i in cards){        
            if( cards[i].n>9){
                continue;
            }
            n += cards[i].n;
        }              
        n = n % 10; 
        return n;
    }//cowCount
    
    /**
     * 玩家uid列表中匹配出胜利者 
     * @param {array} 牌型信息          
     * @return  {array}  [uid]
     */
    this.getMaxPointByPlayer = function(uids){

        G.trace('maxPointByPlayer uids:');
        G.trace(uids);

        var maxPoint = 0;
        var maxPointUids = [];
        for(var ui =0;ui<uids.length;ui++){
            var cuid = uids[ui];                
            G.trace('For:'+cuid);
            G.trace('this.players[ cuid ]');
            G.trace(this.players[ cuid ]);
            G.trace('this.players[ cuid ].point');

            var cPoint = this.players[ cuid ].point;
            G.trace(cPoint);

            if(cPoint<maxPoint){
                continue;
            }
            if(cPoint>maxPoint){
                maxPoint = cPoint;                        
                maxPointUids = [];
            }
            maxPointUids.push(cuid);
        }
        G.trace('maxPointUids='+maxPointUids);
        return maxPointUids;
    }//getMaxPointByPlayer

    

     /**
     * 牛牛结算      
     * @return void
     */
    this.checkout_COW = function(){
        this.over = true;     

        var cardType;
        var cardPointForBanker = 0;//庄家牌积分
        //基础赔率=牛牛基础底注*牛牛庄家倍数
        var baseBet = this.baseBet_COW*this.roomInfo.maxCallBankerOdds_COW;
        G.trace('基础赔率('+baseBet
            +')=牛牛基础底注('+this.baseBet_COW
            +')*牛牛庄家倍数('+this.roomInfo.maxCallBankerOdds_COW
            +') ');
        for(var ui in this.players){

            cardType = this.getCardType_COW(ui);                      
            this.players[ui]['point_COW'] = cardType.point;
            this.players[ui]['cardType_COW'] = cardType.type;
            this.players[ui]['cardTypePoint_COW'] = cardType.typePoint;
            //this.players[ui]['cards_COW'] = G.clone(cardType.cards);
            //手牌倍率
            this.players[ui]['cardOdds_COW'] = CFG.game_cowWater.odds_COW[cardType.type];
       
            if(this.bankerUid_COW==ui){
                cardPointForBanker = cardType.point
            }
        }   

        var bankerWinBet = 0;
        var playerWinBet = 0;
        var playerWinList = {};
        for(var j in this.players){
            if(this.bankerUid_COW==j){
                continue;
            }
            if(cardPointForBanker == this.players[j]['point_COW']){//打和
                
               
                G.trace('庄家打和赢 '); // 只有有牛 还有五小牛      
                var costBet = baseBet * this.players[this.bankerUid_COW]['cardOdds_COW'];
                if(this.players[j].bet<costBet){
                    //玩家爆厂
                    costBet = this.players[j].bet
                }
                this.players[j].bet -= costBet; 
                this.players[j].loseBet_COW = costBet; 
                bankerWinBet += costBet;

                G.trace('玩家扣钱 '+costBet);

                continue;
            }            
            if(cardPointForBanker>this.players[j]['point_COW']){
                //庄家赢钱   按庄家的手牌倍率算       
                G.trace('庄家赢钱   按庄家的手牌倍率算 odds='+this.players[this.bankerUid_COW]['cardOdds_COW']);      
                var costBet = baseBet* this.players[this.bankerUid_COW]['cardOdds_COW'];
                if(this.players[j].bet<costBet){
                    //玩家爆厂
                    costBet = this.players[j].bet
                }
                this.players[j].bet -= costBet; 
                this.players[j].loseBet_COW = costBet; 
                bankerWinBet += costBet;

                G.trace('玩家扣钱 '+costBet);

            }else{
                //普通玩家赢钱  按普通玩家的手牌倍率算     
                G.trace('普通玩家赢钱  按普通玩家的手牌倍率算 uid='+j
                    +' odds='+this.players[j]['cardOdds_COW']);                                
                var costBet = baseBet* this.players[j]['cardOdds_COW'];    
                playerWinBet += costBet;        
                playerWinList[j] = costBet;
                G.trace('玩家赢钱 '+costBet);
            }

        }

        //庄家先赢钱 在分钱给输的
        this.players[this.bankerUid_COW].bet += bankerWinBet;
        this.players[this.bankerUid_COW].winBet_COW = bankerWinBet;

        if(playerWinBet>this.players[this.bankerUid_COW].bet){
            //庄家爆厂
            playerWinBet = this.players[this.bankerUid_COW].bet;
            //如果庄家爆厂 则所有钱按赢钱玩家比例重新分配给赢钱玩家
            G.trace('如果庄家爆厂 则所有钱按赢钱玩家比例重新分配给赢钱玩家');
            var tmp = {};
            var rate;
            var lostBet =0;//余数
            for(var k in playerWinList){
                rate = playerWinList[k]/playerWinBet;//分配比例
                tmp[k] = Math.floor(playerWinBet*rate);
                lostBet += tmp[k];
            }
            lostBet = playerWinBet-lostBet;
            if(lostBet>0){
                //余数给庄家顺时针下面的第一个赢的玩家               
                var nextUid = this.nextBankerPlayer(this.bankerUid_COW,playerWinList);
                tmp[nextUid] += lostBet;
                G.trace('余数给庄家顺时针下面的第一个赢的玩家 nextUid='+nextUid);
            }
            playerWinList = tmp[k];
            G.trace('如果庄家爆厂 赢钱玩家比例playerWinList ');
            G.trace(playerWinList);
        }
        this.players[this.bankerUid_COW].bet -= playerWinBet;
        this.players[this.bankerUid_COW].loseBet_COW = playerWinBet;
        //庄家把钱分给赢钱玩家
        for(var v in playerWinList){            
            this.players[v].bet += playerWinList[v]; 
            this.players[v].winBet_COW = playerWinList[v]; 
        }

        var clientRS = {
            "op":"cow_checkout",
            "result":{},
        };

     
        //php结算信息
        var checkoutRS = {
            "mod"           :"room",
            "game"          :"cowcow",
            "op"       :"cowcow_checkout" ,
            "roomId"   :this.roomId,
            "bankerUid_COW" : this.bankerUid_COW,
            "maxCallBankerOdds_COW" : this.roomInfo.maxCallBankerOdds_COW,
            "result"   :{
            },                                             
            //php
            "bankerSite"    :this.roomInfo.bankerSite            
        };

        var balance;    //牛加水总输赢
        var balance_COW;//牛牛总输赢
        var curPlayer;

        for(var pi in this.players){ 
            curPlayer = this.players[pi];
            //PHP请求参数组织
            balance = curPlayer.winBet_COW-curPlayer.loseBet_COW;
            balance_COW = curPlayer.winBet_COW- curPlayer.loseBet_COW;
            checkoutRS.result[pi] = {                                                 
                "cardType_COW"  : curPlayer.cardType_COW,//牛牛幸运牌型    
                "cards_COW"     : G.cardToNum(curPlayer.cards,'cowWater'),//手牌  牛牛
                "cardTypePoint_COW" : curPlayer.cardTypePoint_COW,
                //"restBet":0,//结算后当前筹码
                "balance_COW" : balance_COW,//牛牛总输赢
                "balance":balance //牛加水总输赢 <0输了 >0赢了
            };

            G.trace('结算牛牛牌型 cards_COW');
            G.trace(checkoutRS.result[pi].cards_COW);

            //操作超时的告诉php 让他站起
            if(curPlayer['offlineTimeout']){
                checkoutRS.result[pi]['timeout'] = 1;
            }
            G.roomObj.delRoomUser(pi);
        }
        G.roomObj.delRoom(this.roomId);

        G.trace('php checkoutRS 参数');
        G.trace(checkoutRS);
       
        //发送信息给PHP结算
        G.phpPost(checkoutRS,function(postRs){

             if(!postRs){                            
                console.error('非法格式:'+postRs);  
                self.toClient({                     
                     "op"    : "opError",
                     "msg"   : "CHECK_ERROR"
                });              
                return;
            }   
            if(postRs.code!=200){                            
                console.error('code:'+postRs.code);
                self.toClient({                     
                     "op"    : "opError",
                     "msg"   : "CHECK_ERROR"
                });
                return;
            }   
            //checkoutRS['players'] = postRs.data.players;            
            
            var clientRS = {
                "op":"cow_checkout",
                "result":{},
            };
            
            var curBanlace = 0;
            var winBet_COW = 0;
            for(var ui in checkoutRS.result){
                let curBet = 0;
                if(postRs.data.players[ui]){
                    curBet = postRs.data.players[ui].bet;
                }else if(postRs.data.standUpPlayers[ui]){
                    curBet = postRs.data.standUpPlayers[ui].bet;
                }else{
                    curBet = 0;
                    console.log('玩家已退出房间,bet=0 uid:'+ui);
                }            

                //赢的筹码 必须是抽水过的
                winBet_COW = self.players[ui].winBet_COW;
                
                //牛家水这边，只有庄家赢了才抽水，返回给客户端的显示
                if(self.bankerUid_COW == ui){
                    winBet_COW -= Math.floor(winBet_COW*self.roomInfo.costScale);
                }
                clientRS.result[ui] = {

                    "cardType_COW":self.players[ui].cardType_COW, //牛牛牌型
                    "cardTypePoint_COW":self.players[ui].cardTypePoint_COW,//牛几

                    "restBet":curBet,//结算后当前筹码
                    "winBet_COW":winBet_COW,//赢的筹码 必须是抽水过的
                    "loseBet_COW":self.players[ui].loseBet_COW,//输的筹码
                    "cards": checkoutRS.result[ui].cards_COW ,//牛牛手牌
                }
            
            }
            clientRS.result[self.bankerUid_COW].isBanker = 1;//牛牛庄家
        
            //筹码不足自动站起的消息必须发送给所有玩家            
            for(var upUid in postRs.data.standUpPlayers){               
                G.trace('掉线或筹码不足自动站起的消息必须发送给所有玩家 standUpPlayer='+upUid);
       
                 //站起原因
                if( self.players[upUid].offlineTimeout ){
                    t = 'timeout';//掉线
                }else{
                    t = 'betLess';//筹码不足
                }

                self.toClient({
                    "uid":upUid,
                    "op":"standUp",
                    "type" : t
                });    

            }
            
            self.toClient(clientRS);
            if(postRs.data.roomOver){
                //如果房间已经结束则告诉客户端
                self.toClient({                   
                    "op":"roomOver"
                });    
            }
            G.trace('游戏结束 clientRS');
            G.trace(clientRS);   
            //自动开始下一局
            G.trace('自动开始下一局');
            G.trace(CFG.game_cowcow.nextStartTimeout);
            G.roomObj.nextStartByRoomId(self.roomId,CFG.game_cowcow.nextStartTimeout);
            
        });
    }//checkout_COW
   
    /**
     * 庄家顺时针下一位在列表中的玩家 结算余数计算用
     * @param {int} bankerUid 庄家uid
     * @param {obj} uidList   赢的玩家列表
     * @return {int} uid
     * @access public
     */
    this.nextBankerPlayer = function(bankerUid,uidList){
        var uid = 0;
        var startSite = this.players[bankerUid].site;
        var nextSite = startSite+1;
        var safeNum = 10;
        while(safeNum>0){    
            safeNum --;           
            if( nextSite == startSite ){
                break;
            }
            if( nextSite == this.roomInfo.site.length ){
                nextSite = 0;
            }
            var cuid = this.roomInfo.site[nextSite];
            if(uidList[cuid]){
                uid = cuid;
                break;
            }
            nextSite++;
        }
        return uid;
    }//nextBankerPlayer
      
    /**
     * 发送消息到对应玩家客户端
     * @param {json} data 发送的数据
     * @param {array} uidArr 发送给的玩家uid数组，不写则发送给所有玩家
     * @return
     * @access public
     */
    this.toClient = function(data,uidArr){
        var channelObj = G.getCH_obj();

        if(!uidArr){
            uidArr = [];
            for(var i in this.players){
                //离开的玩家不需要发送 TODO
                // if(this.players[i].standUp){
                //     continue;
                // }
                uidArr.push(i);
            }
            //旁观者也要发送消息
            for(var j in this.upPlayers){
                //离开的玩家不需要发送 TODO
                uidArr.push(j);
            }
        }else if(!Array.isArray(uidArr)){
            uidArr = [uidArr];
        }

        data['mod'] = 'game_cowcow';
        data['seqNum'] = ++this.seqNum; //服务端消息发送客户端递增序号
        
        for( var i in uidArr){
            channelObj.pushMessageByUids('onData', data, [{'uid':uidArr[i], 'sid':G.frontendId}], function(){});
        }
    }//toClient
};




//根据对象key排序 对象数组
function sortByObj(name){
    return function(o, p){
        var a, b;
        if (typeof o === "object" && typeof p === "object" && o && p) {
            a = o[name];
            b = p[name];
            if (a === b) {
                return 0;
            }
            if (typeof a === typeof b) {
                return a < b ? -1 : 1;
            }
            return typeof a < typeof b ? -1 : 1;
        }
        else {
            throw ("error");
        }
    }
}

