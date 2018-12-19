/**********************
 **    德州-游戏类
 **    @author cxf
 **    @since 2017-06-10
 *************************/

module.exports = function () {
    var self = this;

    this.timeoutRound   = 0;//出牌超时时间 秒
    this.timeoutRoundClient   = 0;//出牌超时时间 秒 -客户端倒计时

    this.cardArr        = [];
    this.players        = {};
    this.upPlayers      = {};
    
    this.maxPlayerNum   = 9; //游戏最大位置/玩家数
    this.playerNum      = 0; //参与游戏的实际玩家数
    this.curPlayerUID   = 0;//当前操作玩家ZZZ

    this.timer = {
        time: 0,
        ts  :0,
        uid :0
    };
   
    this.opUpdateTime = 5;//帧间隔

    this.opUpdateCheckTime = 200; //帧校验间隔

    this.opUpdateCheckMillisecond = 0; // 上次校验时间

    this.underCardArr  = [];//底牌
    this.underCards_i = -1;  //底牌显示索引 0-4
    this.underCardNum = 5;  //5张底牌

    this.addBetNumForRound = 3; //每轮加注次数限制
    this.maxBetNum  = 0; //本轮最大押注数

    this.allBet     = 0; //本手总押注额度    
    this.overBetNum = 0; //本轮剩余加注次数
    this.curBetList = {//本轮押注额列表 
        //"uid1" : 100,
    };
    this.allBetList = {//总押注额列表
        //"uid1" : 100,
    };
    this.allin = {//allin列表
        //"uid1" : 100,
    };    
    this.outPlayers = {//弃牌玩家列表
        //"uid1" : 100,
    };
    //所有牌型
    this.cType      = {
        "SEQ_MAX_SAME_FLOWER_CARD"  :10, //皇家同花顺    A K Q J 10
        "SEQ_SAME_FLOWER_CARD"  :9, //同花顺
        "FOUTH_CARD"            :8, //四条
        "THREE_TWO_CARD"        :7, //葫芦
        "SAME_FLOWER_CARD"      :6, //同花
        "SEQ_CARD"              :5, //顺子
        "THREE_CARD"            :4, //三条
        "DOUBLE_TWO_CARD"       :3, //2对
        "DOUBLE_CARD"           :2, //一对
        "SINGLE_CARD"           :1  //高牌
    };
    this.over = false;//游戏结束标志

    this.sBlindBet = 0; //小盲注额度
    this.bBlindBet = 0; //大盲注额度

    this.bankerSite = 0; //庄家位置
    this.sBlindSite = 0; //小盲注位置
    this.bBlindSite = 0; //大盲注位置

    this.playerCardType = {};//玩家牌型

    this.roomId         = null;
    this.roomInfo       = {};

    this.maxBetLimit = 99999999;//本轮最大押注金额

    //玩家结算牌型
    this.checkoutCards = {
        //"uid2" : [1,2,3,4,5]
    };
    this.opArrForRound = {};//本圈操作记录,用于断线重连
    this.curRound = '';//当前圈
    this.seqNum = 0; //服务端消息发送客户端递增序号

    /**
     * 初始化玩家信息
     * @return void
     * @access public
     */
    this.init = function(roomData){

        G.trace('[init]');
        G.trace(roomData);
       
        //房间状态改为游戏中
        roomData.status = 1; 
        
        this.roomInfo   = roomData; //房间信息
        this.roomId     = this.roomInfo.roomId;
        this.players    = this.roomInfo.players;
        this.upPlayers  = this.roomInfo.upPlayers;


        //房间操作超时时间
        
        this.timeoutRoundClient = this.roomInfo.opTimeout*1;//客户端超时时间
        this.timeoutRound = this.timeoutRoundClient+2 ; //服务端超时时间
        
        this.bBlindBet = this.roomInfo.blindBet*1;                  //小盲注额度
        this.sBlindBet = Math.round(this.roomInfo.blindBet*0.5);   //大盲注额度
        this.roomInfo.process = {};//记录游戏过程
        //初始化牌局玩家信息
        var playerInfo  = this.roomInfo.players;

        this.roomInfo.site = [0,0,0,0,0,0,0,0,0];//座位信息
        this.roomInfo.bankerSite *=1;
        var siteUids = [];
        for(var i in playerInfo){
            this.players[i] = {
                "uid"       : playerInfo[i].uid,      
                "bet"       : playerInfo[i].bet*1,      
                "delayNum"  : playerInfo[i].delayNum*1,//超时道具
                "delayUseNum" : 0,
                "point"	    : 0,
                "cards"		:{},
                "site"	    :playerInfo[i].site*1,
                "enterPool" : 0,//是否入池 入池率
                "opTimeoutNum" : 0, //在线超时次数
                "offlineTimeout" : 0,//掉线超时标志
                "out"       : 0 //是否已弃牌
            }
            this.playerNum++;//参与游戏的实际玩家数

            this.allBetList[i] = 0;
            this.roomInfo.site[playerInfo[i].site] = i;

            siteUids.push(i);
        }        


        //开始游戏之前更换庄家       
        this.bankerSite = this.roomInfo.bankerSite = this.getNextSite( this.roomInfo.bankerSite );        
        
        //根据庄家位置定 小盲注 大盲注
        this.sBlindSite = this.getNextSite( this.bankerSite ); //小盲注位置
        this.bBlindSite = this.getNextSite( this.sBlindSite ); //大盲注位置
        
        //庄家位置
        G.trace('this.bankerSite ：'+this.bankerSite+' UID:'+this.roomInfo.site[this.bankerSite]);

        this.resetCurBet();//清空本轮押注

        var sBlindUid = this.roomInfo.site[this.sBlindSite];
        var bBlindUid = this.roomInfo.site[this.bBlindSite];
        G.trace('押小盲注 '+sBlindUid+'='+this.sBlindBet+' site='+this.sBlindSite)
        this.opBet(sBlindUid, this.sBlindBet,false);//押小盲注
        G.trace('押大盲注 '+bBlindUid+'='+this.bBlindBet+' site='+this.bBlindSite)
        this.opBet(bBlindUid, this.bBlindBet,false);//押大盲注      




        this.toClient({
            "op" : "blind",           
            "sBlindBet" : this.sBlindBet, //小盲注额度
            "sBlindSite" : this.sBlindSite, //小盲注位置
            "bBlindBet" : this.bBlindBet, //大盲注额度
            "bBlindSite" : this.bBlindSite, //大盲注位置
            "bankerSite": this.bankerSite, //庄家位置 ,

            "siteUids"     : siteUids //坐下玩家列表
        });

        
        //第一轮从大盲注下一个开始操作   
             
        this.turnPlayer( this.roomInfo.site[ this.getNextSite(this.bBlindSite) ] );
        G.trace('第一轮从大盲注下一个开始操作 '+this.curPlayerUID);

        //进入底牌圈（preflop）未发公共牌
        this.curRound = 'preflop';

        //生成52张牌 除了大小鬼
        this.createCard();
        //公共牌底牌
        for(var i = 0;i<this.underCardNum;i++){
            this.underCardArr.push( this.cardArr.shift() );
        }
        G.trace('公共牌底牌 5张');
        G.trace(this.underCardArr);
        G.trace('每人发初始底牌 2张');
        //发牌
        this.sendCard();
        G.trace('发牌 结束 暂停2秒 ');


        setTimeout(function(){
            G.trace('开始第一回合 '+self.curPlayerUID);

            self.turn(self.curPlayerUID); //开始回合 第一轮直接指定开始玩家
            G.trace('第一回合 结束 开始初始化定时器');
            
            self.doUpdate(self.roomId);
            G.trace('初始化定时器 over');

            console.log('init game_dzPoker ok roomId：'+self.roomId);

        }, 2000);

        return true;
    };//init

    this.preOpUid = 0;//上一个操作玩家
    //切换下一个操作玩家
    this.turnPlayer = function(uid){
        if( uid!= this.preOpUid){
            //每次切换下一个玩家操作 延时道具使用次数清空
            this.players[uid].timeoutItemUsed = 0;
        }
        this.preOpUid = uid;

        this.curPlayerUID = uid;        

        curUnderCardArr=[];//当前显示的底牌

        for(var i=0;i<=this.underCards_i;i++ ){
            curUnderCardArr.push( G.cardToNum(this.underCardArr[i],'dzPoker') );
        }
        var curPlayerHandCards = G.cardToNum(this.players[this.curPlayerUID]['cards'],'dzPoker');
       

        //坐下玩家uid列表
        var siteUids = [];
        for(var pi in this.players ){
            if(this.players[pi].standUp){
                continue;
            }
            siteUids.push(pi);
        }

        //记录牌局最新过程到房间缓存中 用于外部调用        
        this.roomInfo.process = {
            "siteUids"  : siteUids,//坐下玩家uid列表
            "sBlindBet" : this.sBlindBet, //小盲注额度
            "sBlindSite": this.sBlindSite, //小盲注玩家位置
            "bBlindBet" : this.bBlindBet, //大盲注额度
            "bBlindSite": this.bBlindSite, //大盲注玩家位置
            "bankerSite": this.bankerSite, //庄家位置     

            "curPlayerUID" : this.curPlayerUID,//当前操作玩家uid
           // "curPlayerHandCards" : curPlayerHandCards,//当前操作玩家手牌
              
            "allBet"    : this.allBet,//本手总押注额度               
            "allBetList": this.allBetList,//本手押注额列表   {"uid1" : 100,}
            "curBetList": this.curBetList,//本轮押注额列表   {"uid1" : 100,}
            "allin"     : this.allin,//本手allin情况   {"uid1" : 100,}
            "outPlayers": this.outPlayers,//弃牌玩家列表
            "opArrForRound": this.opArrForRound,//本轮操作情况

            "underCardArr":curUnderCardArr,//当前打开的底牌列表

            "overBetNum" :this.overBetNum,//本轮剩余加注次数
            "maxBetLimit":this.maxBetLimit,//本轮最大押注金额    
            "ts_time"    :G.time(),   
            "ts"         :this.timeoutRoundClient //当前玩家操作倒计时 单位秒
        }

        G.trace('[turnPlayer] 切换下一个操作玩家 '+this.curPlayerUID);

        //下一个玩家开启操作窗口
        this.toClient({
            "op"        :"turnPlayer",
            "overBetNum" :this.overBetNum,//本轮剩余加注次数
            "maxBetLimit" :this.maxBetLimit,//本轮最大押注金额
            "uid" :this.curPlayerUID, //当前操作人 客户端判断uid=自己则显示操作面板，否则隐藏操作面板
            "ts"        :this.timeoutRoundClient //倒计时
        });
    }//turnPlayer
    
    /**
     * 判断当前轮是否可以结束 
     * @return void
     * @access public
     */
    this.roundIsOver = function(){
        G.trace('[roundIsOver]是否可以结束本轮');
        
        //是否所有人都操作过 且筹码一致（排除allin）
        var maxBet = 0;
        var t;
        var tOld = 99999999;

        G.trace('this.curBetList');
        G.trace(this.curBetList);

        for(var i in this.curBetList){
            //已弃牌玩家不需要再判断押注金额
            if(this.players[i].out){
                G.trace('已弃牌玩家不需要再判断押注金额 '+i);
                continue;
            }
            //allin玩家不需要再判断押注金额
            if(this.allin[i]){
                G.trace('allin玩家不需要再判断押注金额 '+i);
                continue;
            }
            t = this.curBetList[i];
            //有人未押注过 此轮未结束
            G.trace('已押注金额 '+i+'='+t);
            if( t==-1 ){
                G.trace('有人未押注过 此轮未结束 '+i);
                return false;
            }
        
            //如果有玩家没有押注到最高押注额度则表示该轮未结束 他需要跟注            
            if(t<this.maxBetNum){                    
                G.trace('如果有玩家没有押注到最高押注额度则表示该轮未结束 他需要跟注');
                return false;
            }                
        }
        G.trace('该轮（圈）可以结束');
        return true;
    }//roundIsOver

     /**
     * 进入下一轮（圈）
     * @return
     * @access public
     */
    this.nextRound = function(){

        this.opArrForRound = {};//复位当前轮操作记录

        //如果所有玩家都弃牌 或者allin 则直接进入结算
        var lastUidNum = 0; //可以操作的剩余玩家数
        for (var i in this.players) {
            if(this.players[i].out){
                continue;
            }   
            if(this.allin[i]){
                continue;
            }
            lastUidNum++; 
        }
        G.trace('[nextRound] 存活玩家数 '+lastUidNum);
        if(lastUidNum<=1){
            //可以操作的玩家数为1 则直接进入结算
            G.trace('[nextRound] 可以操作的玩家数为1 则直接进入结算');
            this.curRound = 'river';    
        }    

 
        switch(this.curRound ){   
        case 'preflop':
            //进入翻牌圈（flop） 同时发出三张公牌
            G.trace('进入翻牌圈（flop）');
            this.curRound = 'flop';
            this.showUnderCards(3);
            break;
        case 'flop':
            //进入转牌圈（turn） 第四张公共牌发出后
            G.trace('进入转牌圈（turn）');
            this.curRound = 'turn';
            this.showUnderCards(4);
            break;
        case 'turn':  
            G.trace('进入河牌圈（river）');
            // 进入河牌圈（river）发第五张公共牌                
            this.showUnderCards(5);
            this.curRound = 'river';
            break;
        case 'river':
        default: //游戏结束
            this.curRound = 'checkout';
            G.trace('进入（checkout）');
            break;
        }


        this.toClient({
            "op" : "nextRound",
            "round" : this.curRound
        });

        if(this.curRound!='checkout'){
            this.resetCurBet();//清空本轮押注

            //进入下一轮清空本轮押注相关信息
            // this.overBetNum = this.addBetNumForRound;
            // this.maxBetNum = 0;
            //除了底牌轮，都是从小盲注开始
            //this.turnPlayer( this.roomInfo.site[this.sBlindSite] );
        }
        
        return this.curRound;
    }//nextRound

     /**
     * 当前回合 转下一个玩家操作
     * @param {int} uid 直接指定进入操作状态的玩家
     * @return
     * @access public
     */
    this.turn = function(uid){  

        //console.error('[turn]'+uid);
        G.trace(this.allBetList);
        //是否可以结束本轮
        
        //判断玩家数是不是只有1个玩家 其他都弃牌或者allin了 如果是则进入结算
        var liveNum = 0;
        for(var i in this.players){ 
            //if( this.players[i].out || this.allin[i] ){
            if( this.players[i].out ){
                continue;
            }                           
            liveNum++;
        }
        if(liveNum<=1){
            this.checkout();
            return;
        }
        

        if( this.roundIsOver() ){            
            G.trace('[turn] roundIsOver 通过 进入下一轮（圈）'+this.curPlayerUID);            
            //进入下一轮（圈）            
            if(this.nextRound()=='checkout'){//直接进入结算
                this.checkout();
                return true;
            }else{
                //除了底牌轮，都是从小盲注开始
                uid = this.roomInfo.site[this.sBlindSite];     
                this.curPlayerUID = uid;
                G.trace('除了底牌轮，都是从小盲注开始 uid='+uid);
               
                if(this.players[uid].out || this.allin[uid] ){
                    G.trace('小盲注玩家已经弃牌或allin则轮换下一个玩家');
                    uid = 0;
                } 
            }
            
        }else{
             G.trace('[turn] roundIsOver 本轮未结束'+this.curPlayerUID);            
        }

     
        //下一个玩家出牌           
        if(!uid){
            uid = this.curPlayerUID;
            var curUID = this.curPlayerUID ;
            G.trace('[turn] 寻找 '+this.curPlayerUID+' 的下一个玩家'); 
           
            for(var i in this.players){                
                uid = this.getNextPlayerUID(uid);
                G.trace('下一个玩家是 '+uid);   
                //如果下一个玩家已弃牌或alli 则不能再操作                
                if( this.players[uid].out || this.allin[uid] ){   
                    G.trace('下一个玩家已弃牌或allin 则不能再操作 '+uid);                
                    continue;
                }              
                //已经轮了一圈还是自己表示没有可以操作的人了 则进行结算
                if(uid == curUID){
                    G.trace('已经轮了一圈还是自己表示没有可以操作的人了 则进行结算 '+uid);   
                    this.checkout();
                    return ;
                }                
                break;
            }             
        }
        
        this.turnPlayer(uid); 
        G.trace('开始重新倒计时 操作者 :'+this.curPlayerUID);
        //开始重新操作倒计时
        this.setTimer( function(){
            G.trace('turn 倒计时超时 ');
            self.opTimeout();
        });
        return true;
    }//turn

    /***
     * 操作超时
     * @return
     */
    this.opTimeout = function(){
        var curUID = this.curPlayerUID ;
        
        G.trace('opTimeout 超时 '+curUID);
        
        //是否有可以使用的倒计时延时道具
        if( this.players[curUID].delayNum>0 ){

            //延时道具1回合可以使用一次
            if(!this.players[curUID].timeoutItemUsed){                
                this.players[curUID].timeoutItemUsed = 1;
                G.trace('延时道具1回合可以使用一次 '+curUID);

                this.players[curUID].delayNum--;
                this.players[curUID].delayUseNum++;
                
                this.turn(curUID);
                return;
            }
        }    

        //超时处理  判断当前状态
        this.players[curUID].opTimeoutNum ++;//增加超时次数

        //如果是允许让牌则让牌 : 本人押注达到本轮最高押注数则可以让牌
        if(this.curBetList[curUID]==-1){
            this.curBetList[curUID] = 0;
        }
        if( this.curBetList[curUID]>=this.maxBetNum ){            
            //让牌
            G.trace('超时 让牌 :'+curUID);

            this.pass();
        }else{            
            //不能让牌就弃牌    
            G.trace('超时 弃牌 :'+curUID);
            //this.players[curUID]['timeout'] = 1;
            this.players[curUID].opTimeoutNum = 3;//超时弃牌 则结算后就要站起
            this.out();
        }
        
    }//opTimeout

    /***
     * 每帧执行
     * @return
     */

    this.doUpdate = function(roomId){
        var millisecond = G.millisecond();
        if (this.opUpdateCheckMillisecond + this.opUpdateCheckTime < millisecond) {

            if( this.over ){
                G.trace('[OVER]');
                return;
            }

            //判断玩家数是不是只有1个玩家 其他都弃牌或者allin了 如果是则进入结算
            var liveNum = 0;
            for(var i in this.players){ 
                //if( this.players[i].out || this.allin[i] ){
                if( this.players[i].out ){
                    continue;
                }                           
                liveNum++;
            }
            if(liveNum<=1){
                this.checkout();
                return;
            }

            //如果当前操作状态下的玩家突然直接站起退出 则直接进入下一轮        
            if( this.players[this.curPlayerUID].out ){
                this.turn();
                if( this.over ){
                    G.trace('[OVER]');
                    return;
                }
            }

            //有定时器
            if( !this.timer.stop ){
                var now     = G.time();
                if(now> this.timer.time){
                    var curTs   = this.timer.ts - (now-this.timer.time);
                    this.timer.time = now;
                    this.timer.ts   = curTs;               
                    if( curTs<= 0 ){ //定时器时间到 执行定时事件
                        G.trace('[定时处理]');
                        this.delTimer();
                        this.timer.fun();
                        //游戏结束退出函数
                        if(this.over){
                            return ;
                        }
                    }
                }
            }else{
                G.trace('[no Time]');
            }
            this.opUpdateCheckMillisecond = millisecond;

        }

        //读取玩家操作请求 
        var opArr = this.roomInfo.opArr;
        var opInfo;
        while(opArr.length>0){

            if(this.over){
                break;
            }

            opInfo = opArr.shift();
            if(opInfo['uid']!=this.curPlayerUID){//操作过期
                G.trace('操作过期，当前未轮到你操作 uid='+opInfo['uid']+' curPlayerUID='+this.curPlayerUID);
                this.toClient({
                    "op"        :'opError',                  
                    "opid"      : opInfo.opid,     
                    "msg"       :'DZ_NO_TURN_YOU' 
                } , opInfo['uid']);
                continue;
            }           

            G.trace('读取玩家操作请求');
            G.trace(opInfo);           

            //超时操作次数归零
            if( this.players[opInfo['uid']].opTimeoutNum >0 ){
                this.players[opInfo['uid']].opTimeoutNum =0;
            }

            switch(opInfo.op){ 
            case 'addBet'://押注
                //加注 剩余加注次数不足不允许加注 ，只能跟注
                if(opInfo.bet>this.maxBetNum && this.overBetNum<1){   
                    G.trace('本轮加注次数超过限制，不允许加注 uid='+opInfo['uid']);
                    this.toClient({
                        "op"        :'opError',         
                        "opid"      : opInfo.opid,              
                        "msg"       :'DZ_ADD_BET_MAX' 
                    } , opInfo['uid']);    
                    continue;
                }

                //押注必须达到最高押注数,否则不能跟注
                var tmpBet = this.curBetList[opInfo['uid']];
                if(tmpBet<0){
                    tmpBet = 0;
                }
                tmpBet += opInfo.bet;
                if( this.maxBetNum>0 && tmpBet< this.maxBetNum ){    
                    G.trace('加注金额不足 uid='+opInfo['uid']+' bet='+opInfo.bet);
                    G.trace('this.maxBetNum='+this.maxBetNum+' tmpBet='+tmpBet);
                    this.toClient({
                        "op"        :'opError',  
                        "opid"      : opInfo.opid,                     
                        "msg"       :'DZ_ADD_BET_LESS'
                    } , opInfo['uid']); 
                    continue;
                }

                this.addBet(opInfo.bet);
                break;
            case 'followBet': //跟注
                this.followBet();
                break;
            case 'pass'://让牌 本人押注达到本轮最高押注数则可以让牌
                
                if( this.maxBetNum>0 && this.curBetList[opInfo['uid']] < this.maxBetNum ){    
                    G.trace('让牌条件不足 uid='+opInfo['uid']);
                    this.toClient({
                        "op"        :'opError',  
                        "opid"      : opInfo.opid,                     
                        "msg"       :'DZ_PASS_ERROR'
                    } , opInfo['uid']); 
                    continue;
                }
                this.pass();
                break;
            case 'allin'://all in 
                this.allin();
                break;
            case 'out'://弃牌退出           
                G.trace('手动 弃牌 uid='+opInfo['uid']);
                this.out();
                break;
            default:
                break;
            }
           //游戏结束退出循环
            if(this.over){
                return ;
            }
        }

        //游戏结束 或 进入下一个轮询
        if(this.over){
            return ;
        }else{
            setTimeout(function(){
                self.doUpdate(roomId);
            }, this.opUpdateTime);
        }
    }//doUpdate

    /**
     * 设置定时器
     * @param {string} callback 回调函数名
     * @param {int} ts 剩余时间
     * @param {int} uid
     * @return
     * @access public
     */
    this.setTimer = function(callback,ts,uid){
        ts = ts||this.timeoutRound;
       // ts *= 1000;//时间戳
        this.timer = {         
            time: G.time(),
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
        for (var i=0; i<4; ++i){  //0=redHeart ,1=redBlock, 2=blackHeart,3=blackGrass
            for (var j=0; j<13; ++j){
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
        G.randomAry( this.cardArr );

    }//washCard

    /**
     * 发牌
     * @return void
     * @return
     * @access public
     */    
    this.sendCard = function(){
        var tmpCard,cardKey;
        //每个人发2张
        var preNum = 2;

        for(var p in this.players){
            G.trace('每个人发2张:'+p);
            this.players[p]['cards'] = [];

            for(var i=0;i<preNum;i++){
                tmpCard = this.cardArr.shift();           
                this.players[p]['cards'].push(tmpCard);                
            }

            this.toClient({
                "op"        :'self_showCards',            
                //"cards"     :this.players[p]['cards']
                "cards"     :G.cardToNum(this.players[p]['cards'],'dzPoker')
            } ,this.players[p]['uid'] );
        }
        return true;
    }//sendCard

    /**
     * 押注
     * @return
     * @access public
     * @access public
     */
    this.addBet = function(bet){
        this.opArrForRound[this.curPlayerUID] = 'addBet';
        this.opBet(this.curPlayerUID,bet,true);  
        this.turn();
    }//addBet

    /**
     * 跟注
     * @return
     * @access public
     * @access public
     */
    this.followBet = function(){
        this.opArrForRound[this.curPlayerUID] = 'followBet';

        G.trace('跟注 followBet 操作 '+this.curPlayerUID);
        //必须跟最高押注金额
        if(this.curBetList[this.curPlayerUID]==-1){
            this.curBetList[this.curPlayerUID] = 0;
        }
        //跟注差额 = 最高押注-本轮已押
        var mustBet = this.maxBetNum - this.curBetList[this.curPlayerUID];
        this.addBet(mustBet);                
    }//followBet

    /**
     * allin全押     
     * @return
     * @access public
     * @access public
     */
    this.allin = function(){        
        this.addBet( this.players[this.curPlayerUID].bet );        
    }//allin
    
    /**
     * 让牌
     * @return
     * @access public
     * @access public
     */
    this.pass = function(){
        this.addBet(0);  
    }//pass

    /**
     * 弃牌           
     * @return
     * @access public
     */
    this.out = function(){
        this.players[this.curPlayerUID].out = 1;
        this.outPlayers[this.curPlayerUID] = 1;
        this.toClient({
            'op'    : 'out',
            'uid'   : this.curPlayerUID
        });   
        this.turn();
    }//out

    /**
     * 游戏操作退出站起           
     * @return
     * @access public
     */
    this.outAndStandUp = function(uid){
        this.players[uid].out = 1;
        this.outPlayers[uid] = 1;
        this.toClient({
            'op'    : 'out',
            'uid'   : uid
        });   

        //TODO

    }

    /**
     * 操作筹码
     * @param {int} uid 
     * @param {int} bet 增加的筹码 
     * @param {bool} clientMsg   true=发送客户端消息   false=小盲注和大盲注 不发送 
     * @return void
     * @access public
     */
    this.opBet = function(uid,bet,clientMsg){
        G.trace('[opBet] uid:'+uid+' bet'+bet);     
        G.trace(this.curBetList);   

        //如果未操作过则设置为0
        if(this.curBetList[uid] == -1){
            this.curBetList[uid] = 0;   
        }

        if(bet>0){
            //入池率 操作
            if( clientMsg && !this.players[uid].enterPool ){
                this.players[uid].enterPool = 1;
            }

            //扣除身上金钱
            if(bet>=this.players[uid].bet){ //金钱不够  allin  

                G.trace('[opBet] 金钱不够  allin  ');             
                bet = this.players[uid].bet;
                this.allin[uid] = this.allBetList[uid]+bet;    
                if(clientMsg){
                    this.toClient({
                        "op"    : "allin",
                        "uid"   : uid,
                        "bet"   : bet
                    });           
                }  
            }
            this.players[uid].bet -= bet; //扣除身上的筹码
            this.curBetList[uid] += bet;//每个玩家当前轮押注
            this.allBetList[uid] += bet;//每个玩家的总押注

            this.allBet += bet*1;//总押注额度
                         
            G.trace('总押注额度 this.allBet='+this.allBet);
            if(clientMsg && !this.allin[uid]){
                 if(this.maxBetNum==0){
                    this.opArrForRound[uid] = 'firstBet';
                    //本圈第一次押注
                    this.toClient({
                        //"op"    : "firstBet",
                        "op"    : "addBet",
                        "uid"   : uid,
                        "bet"   : bet
                    }); 
                }else if(this.curBetList[uid]>this.maxBetNum){
                    this.opArrForRound[uid] = 'addBet';
                    this.overBetNum --;//加注次数增加

                    //加注
                    this.toClient({
                        "op"    : "addBet",
                        "uid"   : uid,
                        "bet"   : bet
                    });                            
                }else{
                    this.opArrForRound[uid] = 'followBet';
                    //跟注
                    this.toClient({
                        "op"    : "followBet",
                        "uid"   : uid,
                        "bet"   : bet
                    });  
                }                
            }
             //更新本轮最大押注额度
            if(this.curBetList[uid]>this.maxBetNum){
                this.maxBetNum = this.curBetList[uid];
            }
        }else{

            G.trace('[opBet] 过牌');
            G.trace(this.allBetList);
            //过牌 只从-1标注为0 表示操作过
            this.opArrForRound[uid] = 'pass';
            this.toClient({
                "op"    : "pass",
                "uid"   : uid
            });   
        }  

        G.trace('[opBet] allBetList');
        G.trace(this.curBetList);
    }//opBet

    /**
     * 进入下一轮清空本轮押注相关信息
     * @return
     * @access public
     */
    this.resetCurBet = function(){
        for(var i in this.players){
            this.curBetList[i] = -1; //设置为未押注状态
        }
         //进入下一轮清空本轮押注相关信息
        this.overBetNum = this.addBetNumForRound;
        this.maxBetNum = 0;
    }//resetCurBet

    /**
     * 显示底牌
     * @param {int} cardNum 显示到第几张底牌
     * @return
     * @access public
     */
    this.showUnderCards = function(cardNum){
        G.trace('[showUnderCards]显示底牌 '+cardNum);
        var cardShowArr = [];
        if(cardNum){
            this.underCards_i = cardNum-1;    
        }
        for(var i=0;i<=this.underCards_i;i++){
            cardShowArr[i] = this.underCardArr[i];
        }
        //亮出给所有玩家看
        this.toClient({
            "op":"showUnderCards",
            //"cards":cardShowArr
            "cards" :G.cardToNum(cardShowArr,'dzPoker')
        });
    }//showUnderCards

    /**
     * 取牌类型
     * @param {uid} 玩家uid
     * @return {array} 牌类型        
     * @access public
     */
    this.getCardType = function(uid){
        //5+2 = 7张牌 评出最大的牌型
        
        G.trace('[getCardType] 取牌型 大小 uid='+uid);
        var cards = [];
        for(var ci in this.players[uid]['cards']){            
            cards.push(this.players[uid]['cards'][ci]);
        }
        G.trace('[getCardType] 手牌');
        G.trace(cards);
        G.trace('[getCardType] 底牌');
        G.trace(this.underCardArr);
        for(var ui=0;ui<this.underCardArr.length;ui++){    

            cards.push(this.underCardArr[ui]);
        }
        G.trace('[getCardType] 底牌加手牌 uid='+uid);
        G.trace(cards);

        G.trace(showCardTxt(cards));
        
        //牌值 : 牌列表 
        var listByCardValue = {
                        
        }

        var flowerOfCards = [ [],[],[],[] ];//0=redHeart ,1=redBlock, 2=blackHeart,3=blackGrass

        var cardNum = 0;
        var tmp1 ;        
        for(var i=0;i<cards.length;i++){            
            tmp1 = cards[i].n;
            // if( listByCardValue[tmp1] ){
            //     listByCardValue[tmp1]++;
            // }else{
            //     listByCardValue[tmp1] = 1;
            // }
            
            if( !listByCardValue[tmp1] ){
                listByCardValue[tmp1] = [];
            }
            listByCardValue[tmp1].push( cards[i] );
            

            flowerOfCards[ cards[i].c ].push( cards[i] );
            cardNum++;
        }
        G.trace('[getCardType] flowerOfCards');
        G.trace(flowerOfCards);
        //按牌数(单,对子,三条) 整理好
        var listByCardType = {
            0 : [],
            1 : [],
            2 : [],
            3 : [],
            4 : []
        };

        for(var j in listByCardValue){
            listByCardType[ listByCardValue[j].length ].push(j*1); //当前牌值的牌数  //'牌数' : 牌值
        }
        G.trace('[getCardType] listByCardType');
        G.trace(listByCardType);
 
        //四条
        if( listByCardType[4].length===1 ){
            var fouthCardValue = listByCardType[4][0];
            //主牌型评分
            var tmpNum = this.cType.FOUTH_CARD *1000000
                        + fouthCardValue * 300;

            //结算手牌
            var checkoutCards = [
                    { "c":0,"n":fouthCardValue },
                    { "c":1,"n":fouthCardValue },
                    { "c":2,"n":fouthCardValue },
                    { "c":3,"n":fouthCardValue }
                ];
            //加一张除了4条外最大点数牌 凑成5张             
            var maxCardValue = 0;  
            var maxCardIndex ;
            for(var i=0;i<cards.length;i++){   
                if(fouthCardValue == cards[i].n){
                    continue;
                }         

                if(maxCardValue<cards[i].n){
                    maxCardIndex = i;
                    maxCardValue = cards[i].n;
                }
            }

            checkoutCards.push({
                "c": cards[maxCardIndex].c,
                "n": cards[maxCardIndex].n
            });

            return {
                "type"      : 'FOUTH_CARD',
                //"maxValue"  : listByCardType[4][0],
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }
        //葫芦
        if( listByCardType[3].length=== 1 && listByCardType[2].length===1){
            var threeCardValue  = listByCardType[3][0];
            var twoCardValue    = listByCardType[2][ listByCardType[2].length-1 ];
            //主牌型评分
            var tmpNum = this.cType.THREE_TWO_CARD *1000000
                + threeCardValue * 300;
            //副牌型评分
            //listByCardType[2].sort();//从小到大排序           
            tmpNum += twoCardValue * this.cType.DOUBLE_CARD*100 ;//  总评分 = 主牌+副牌

            //结算手牌
            var checkoutCards = [];            
            for(var i=0;i<cards.length;i++){   
                if(threeCardValue == cards[i].n){
                    checkoutCards.push(cards[i]);
                }         
            }
            for(var i=0;i<cards.length;i++){   
                if(twoCardValue == cards[i].n){
                    checkoutCards.push(cards[i]);
                }         
            }

            return {
                "type"      : 'THREE_TWO_CARD',
                //"maxValue"  : listByCardType[3][0],
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }

        //是否是同花
        var sameFlowerKey = false;
        for( var ci in flowerOfCards){
            if(flowerOfCards[ci].length>=5){
                sameFlowerKey = ci;
                break;
            }
        }
        G.trace('[getCardType] 是否是同花');
       
        if( sameFlowerKey !== false ){//同花

            G.trace('[getCardType] 同花牌型处理');

            var sameFlowerCards = flowerOfCards[ sameFlowerKey ];//cards

            var seqInfo_flower  = isSeqCards( sameFlowerCards );

            var seqCards_flower = seqInfo_flower.cards;

            //结算手牌
            var checkoutCards = [];
            for(i=1;i<=5;i++){
                checkoutCards.push( seqCards_flower[seqCards_flower.length-i] );
            }

            if(seqInfo_flower.seq) {//同花顺
                //判断是否是皇家同花顺 最大是否是A
                G.trace('[getCardType] 判断是否是皇家同花顺 最大是否是A');
              
                if( seqCards_flower[ seqCards_flower.length-1 ].n ===12 ){//皇家同花顺
                    G.trace('皇家同花顺');                   
                    return {
                        "type"      : 'SEQ_MAX_SAME_FLOWER_CARD',
                        //"maxValue"  : seqCards_flower[ seqCards_flower.length-1 ].n,
                        "point"     : 99999999, //最大牌积分
                        "cards"     : checkoutCards
                    };
                }else{//同花顺
                    G.trace('同花顺');
                    //主牌型评分
                    var tmpNum = this.cType.SEQ_SAME_FLOWER_CARD *1000000
                      //  + listByCardType[1][0] * 300
                        + seqCards_flower[ seqCards_flower.length-1 ].n*100;

                    return {
                        "type"      : 'SEQ_SAME_FLOWER_CARD',
                        //"maxValue"  : seqCards_flower[ seqCards_flower.length-1 ].n,
                        "point"     : tmpNum,
                        "cards"     : checkoutCards
                    };
                }
            }else{
                //同花
                //主牌型评分
                G.trace('同花');
                var tmpNum = this.cType.SAME_FLOWER_CARD *1000000
                   // + listByCardType[1][0] * 300
                    + seqCards_flower[ seqCards_flower.length-1 ].n*10000
                    + seqCards_flower[ seqCards_flower.length-2 ].n*1000
                    + seqCards_flower[ seqCards_flower.length-3 ].n*100
                    + seqCards_flower[ seqCards_flower.length-4 ].n*10
                    + seqCards_flower[ seqCards_flower.length-5 ].n*1;
                return {
                    "type"      : 'SAME_FLOWER_CARD',
                    //"maxValue"  : seqCards_flower[ seqCards_flower.length-1 ].n,
                    "point"     : tmpNum,
                    "cards"     : checkoutCards
                };
            }
        }

        var seqInfo  = isSeqCards( G.clone( cards ) );
        var seqCards = seqInfo.cards;
        G.trace('不是同花 ');
        if(seqInfo.seq) {//顺子
            G.trace('顺子 ');
            //主牌型评分
            var tmpNum = this.cType.SEQ_CARD *1000000
               // + listByCardType[1][0] * 300
                + seqCards[ seqCards.length-1 ].n*100;

            //结算手牌
            var checkoutCards = [];
            for(i=1;i<=5;i++){
                checkoutCards.push( seqCards[seqCards.length-i] );
            }
            return {
                "type"      : 'SEQ_CARD',
                //"maxValue"  : seqCards[ seqCards.length-1 ].n,
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }
        G.trace('不是顺子 ');
        //三条
        if( listByCardType[3].length===1 ){
            G.trace('三条 ');
            var threeCardValue = listByCardType[3][0];
            //主牌型评分
            var tmpNum = this.cType.THREE_CARD *1000000
                + threeCardValue * 300;
            //副牌型评分
            //listByCardType[1].sort();//从小到大排序

            var cardValue1 = listByCardType[1][ listByCardType[1].length-1 ];//最大单牌值
            var cardValue2 = listByCardType[1][ listByCardType[1].length-2 ];//第2大单牌值

            tmpNum += cardValue1 *100
                    + cardValue2 *10;

            //结算手牌 
            var checkoutCards = [];            
            for(var i=0;i<cards.length;i++){   
                if(threeCardValue == cards[i].n){
                    checkoutCards.push(cards[i]);
                }         
            }            
            checkoutCards.push( listByCardValue[cardValue1][0] );
            checkoutCards.push( listByCardValue[cardValue2][0] );

            return {
                "type"      : 'THREE_CARD',
                //"maxValue"  : listByCardType[3][0],
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }
        G.trace('不是三条 ');
        //2对
        if( listByCardType[2].length>=2 ){
            //listByCardType[2].sort();//从小到大排序
            G.trace('2对 ');
            var twoCardValue1 = listByCardType[2][listByCardType[2].length-1];
            var twoCardValue2 = listByCardType[2][listByCardType[2].length-2];
            //主牌型评分
            var tmpNum = this.cType.DOUBLE_TWO_CARD *1000000               
                + twoCardValue1 * 10000 //主要看最大的对
                + twoCardValue2 * 1000 ;//第2大的对
                
            
            //取单张最大         
            var oneCardValue = listByCardType[1][ listByCardType[1].length-1 ];      
            //如果有3对则把最小一对 当做 单张最大进行计算
            if(listByCardType[2].length>3 && listByCardType[2][0]>oneCardValue){
                oneCardValue = listByCardType[2][0];
            }
            //副牌型评分
            tmpNum +=  oneCardValue*100;

            //结算手牌 
            var checkoutCards = [];            
            for(var i in listByCardValue[twoCardValue1]){                   
                checkoutCards.push( listByCardValue[twoCardValue1][i] );                
            }            
            for(var i in listByCardValue[twoCardValue2]){                   
                checkoutCards.push( listByCardValue[twoCardValue2][i] );                
            }            
            checkoutCards.push( listByCardValue[oneCardValue][0] );            

            return {
                "type"      : 'DOUBLE_TWO_CARD',
               // "maxValue"  : listByCardType[2][1],
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }
        G.trace('不是2对 ');
        //1对
        if( listByCardType[2].length===1 ){
            G.trace('是1对 ');
            var twoCardValue = listByCardType[2][0];
            //主牌型评分
            var tmpNum = this.cType.DOUBLE_CARD *1000000
                + twoCardValue * 10000;

            //副牌型评分
            var cardValue1 = listByCardType[1][ listByCardType[1].length-1 ];
            var cardValue2 = listByCardType[1][ listByCardType[1].length-2 ];
            var cardValue3 = listByCardType[1][ listByCardType[1].length-3 ];
            tmpNum += cardValue1 *100
                    + cardValue2 *10
                    + cardValue3 *1;


             //结算手牌 
            var checkoutCards = [];            
            for(var i in listByCardValue[twoCardValue]){                   
                checkoutCards.push( listByCardValue[twoCardValue][i] );                
            }            
                 
            checkoutCards.push( listByCardValue[cardValue1][0] );      
            checkoutCards.push( listByCardValue[cardValue2][0] );      
            checkoutCards.push( listByCardValue[cardValue3][0] );      

            return {
                "type"      : 'DOUBLE_CARD',
               // "maxValue"  : listByCardType[2][0],
                "point"     : tmpNum,
                "cards"     : checkoutCards
            };
        }
        G.trace('不是1对 ');          
        G.trace('高牌 ');
        G.trace(listByCardType[1]);  
        //高牌        
        G.trace('listByCardType');
        G.trace(listByCardType);     
        
        
        var cardValue1 = listByCardType[1][ listByCardType[1].length-1 ];
        var cardValue2 = listByCardType[1][ listByCardType[1].length-2 ];
        var cardValue3 = listByCardType[1][ listByCardType[1].length-3 ];
        var cardValue4 = listByCardType[1][ listByCardType[1].length-4 ];
        var cardValue5 = listByCardType[1][ listByCardType[1].length-5 ];

        var tmpNum = this.cType.SINGLE_CARD *1000000
            + cardValue1 *10000
            + cardValue2 *1000
            + cardValue3 *100
            + cardValue4 *10
            + cardValue5 *1;
        var checkoutCards = [
                listByCardValue[cardValue1][0],
                listByCardValue[cardValue2][0],
                listByCardValue[cardValue3][0],
                listByCardValue[cardValue4][0],
                listByCardValue[cardValue5][0]
            ];
        return {
            "type"      : 'SINGLE_CARD',
            //"maxValue"  : listByCardType[1][ listByCardType[1].length-1],
            "point"     : tmpNum,
            "cards"     : checkoutCards
        };
    }//getCardType

    /**
     * 对比牌大小
     * @param {int} pIndex 玩家index
     * @param {obj} cards  出牌列表 例:对4= { '4-0' , '牌值4-方块' }
     * @return {int} hasPlayerNum 返回比牌人数
     * @access public
     */   
    this.compCard = function(){
            
        //var pointList = {};
        var curPoint=0 ;
        var cardType;

        G.trace('[compCard] 开始对比牌大小');
        var hasPlayerNum = 0;
        for(var i in this.players){
            //弃牌的玩家不参与最后比牌
            if(this.players[i].out){
                this.checkoutCards[i] = [];
                continue;
            }           
            G.trace('getCardType');
            cardType = this.getCardType(i);   
            this.playerCardType[i]=cardType.type;//存储玩家牌型
            this.checkoutCards[i] = cardType.cards; 

            G.trace('cardType:');         
            G.trace(cardType);
             // cardType = 
            // {
            //     "type"      : this.cType.SINGLE_CARD,
            //     "maxValue"  : listByCardType[1][ listByCardType[1].length-1],
            //     "point"     : tmpNum,
            //     "cards"     : listByCardType[1]
            // }
            curPoint = cardType.point;
           
            this.players[i].point = curPoint;
    
            hasPlayerNum ++;
            // if( !pointList[curPoint] ){
            //     pointList[curPoint] = [];
            // }
            // pointList[curPoint].push(this.players[i].uid);
        }

        if(hasPlayerNum>1){
            this.underCards_i = 4;//有进入最后一轮结算则 底牌都显示  
        }
        
        return hasPlayerNum;//返回比牌人数
        // var rs = [];
        // for (var pi in pointList) {
        //     rs.push({
        //         "uidArr" : pointList[pi],
        //         "point"  : pi*1 
        //     });
        // }
        // G.trace('[compCard] RS')
        // G.trace(rs)
        // return rs;    
    }//compCard

    /**
     * 取下一个site
     * @param {int} cSite  当前site的下一个
     * @return int nSite 下一个site
     * @access public
     */
    this.getNextSite = function(cSite){
       
        var siteArr = this.roomInfo.site;
        var siteNum = siteArr.length;

        var nSite = cSite;
        for (var i = 0; i <siteNum; i++) {
            var nSite = nSite+1;
            //过头则从头开始
            if(nSite>=siteNum){
                nSite = 0;
            }                
            //找到下一位不为空的位置
            if(siteArr[nSite]>0){
                break;
            }                
        }
        return nSite;
    }//getNextSite

     /**
     * 取下一个玩家uid
     * @param {int} uid  玩家uid
     * @return
     * @access public
     */
    this.getNextPlayerUID = function(uid){
        var site1 = this.players[uid].site;
        var site2 = this.getNextSite(site1);

        return this.roomInfo.site[site2];
    }//getNextPlayerUID
      
    /**
     * 游戏结束结算
     * @param {int} pIndex 玩家index
     * @return
     * @access public
     */
    this.checkout = function(){
        this.over = true;  
        
        G.trace('[checkout] compCarding');
        
        //牌型大小排行
        var compCardUserNum = this.compCard();//返回参与最终比牌人数
        G.trace('[checkout] compCard over');

        //每个玩家赢得的筹码列表
        var userWinBetArr = {};
        for(var i in this.players){
            userWinBetArr[i] = 0;
        }

        //TODO 无效押注  加注超过
        //不算抽水结算
        //最后一轮押注情况中 如果最高押注金额只有一个人那么 溢出第2高押注的部分
        //为无效押注 不参与抽水结算 


       
        var maxBetFir = 0;//押注最高额度
        var maxBetFirUid = 0;
        var maxBetSed = 0;//押注第2高
        var maxBetSedUid = 0;
        for(var mi in  this.allBetList){
            
            if(maxBetFir<this.allBetList[mi]){
                maxBetSed    = maxBetFir;
                maxBetSedUid = maxBetFirUid;

                maxBetFir    = this.allBetList[mi];
                maxBetFirUid = mi;
            }else if(maxBetSed<this.allBetList[mi]){
                maxBetSed    = this.allBetList[mi];
                maxBetSedUid = mi;
            }
        }

        var unuseBet = maxBetFir-maxBetSed;
        G.trace('最高押注额度 maxBetFirUid='+maxBetFirUid);

        if(unuseBet>0){
            G.trace('最高押注额度只有一人 则要溢出第2名的部分为无效押注：'+unuseBet);
            //最高押注额度只有一人 则要溢出第2名的部分为无效押注
            this.allBet -= unuseBet;
            this.allBetList[maxBetFirUid] -= unuseBet;
            if(this.allin[maxBetFirUid]){ 
                //如果最高押注的人参与allin 则当前allin需要做无效化判断
                 G.trace('如果最高押注的人参与allin 则当前allin需要做无效化判断：'+maxBetFirUid);
           
                delete this.allin[maxBetFirUid];
            }
        }

        G.trace(this.allBetList);

        var allBetList2 = G.clone(this.allBetList);

        //组织边池 每次allin增加一个边池
        var poolArr = {};
        var curAllBet = this.allBet;
        G.trace('[checkout] 组织边池 每次allin增加一个边池');
        G.trace('curAllBet '+curAllBet);
      
        var allinArr    =   [];
        for (var i in this.allin) {   
            allinArr.push(this.allin[i]);
        }
        allinArr.sort(function(m,n){
            return m-n;
        });
        G.trace('allinArr');
        G.trace(allinArr);
        
        var allin_old = 0;
        var allinBet = 0;
        for (var i in allinArr) {   
            //当前allin的玩家所有押注,用来做分池标准           
            allinBet = allinArr[i];              

            G.trace('[allinArr] '+i+' ='+allinBet);

            if(poolArr[allinBet]){//当前金额的allin池已经存在则跳过
                continue;
            }

            poolArr[allinBet] = {
                "bet":0,
                "uidArr":[]
            };
            var curPlayerAllBet = 0;
            var curAllinLvBet=0;
            for (var j in this.allBetList) {   

                if(this.allBetList[j]==0){
                    G.trace('钱不足跳过当前轮边池分配 uid:'+j);
                    continue;
                }

                //当前玩家本手所有押注          
                curPlayerAllBet = this.allBetList[j]; 
                G.trace('当前玩家本手所有押注 uid='+j+' curPlayerAllBet='+curPlayerAllBet);                    
                G.trace('allinBet('+allinBet+')-allin_old('+allin_old+')='+(allinBet-allin_old));  
                
                curAllinLvBet = allinBet-allin_old;

                if(curAllinLvBet > curPlayerAllBet){
                    G.trace('玩家本手所有押注不足allin，则表示这个玩家是本轮弃牌，则全扣剩余押注，并不参与分配奖池');
                    //玩家本手所有押注不足allin，
                    //则表示这个玩家是本轮弃牌，则全扣剩余押注，并不参与分配奖池
                    poolArr[allinBet].bet += curPlayerAllBet;
                    this.allBetList[j] = 0;                        
                }else{
                    //金额足够扣，则直接扣掉allin押注额度，并参与奖池分配
                    
                    poolArr[allinBet].bet += curAllinLvBet;                    
                    this.allBetList[j] -= curAllinLvBet;  
                    //只有未弃牌的玩家才能参与奖池分配
                    if(!this.players[j].out){
                        poolArr[allinBet].uidArr.push(j);
                    } 
                }
            }
            //总押注减去allin的金额来判断是否有剩余溢出的押注额度作为最后边池
            curAllBet -= poolArr[allinBet].bet;
            allin_old += curAllinLvBet;
        }    

        if(curAllBet>0){
            var overUidArr = [];//allin处理后还有剩余押注额的玩家，用于组织最后边池
            for (var ouid in this.allBetList) { 
                //只有未弃牌的玩家才能参与奖池分配                
                if(this.allBetList[ouid]>0 && !this.players[ouid].out){
                    overUidArr.push(ouid);
                }
            }
            //最后边池
            G.trace('最后边池 '+curAllBet);
            G.trace('overUidArr :');
            G.trace(overUidArr);
            poolArr['last'] = {
                "bet" : curAllBet,
                "uidArr" : overUidArr
            };       
        }
 
        //循环注池 分配奖金
        G.trace('循环注池 分配奖金');
        G.trace(poolArr);
        for(var i in poolArr){
            G.trace('poolArr '+i);
            G.trace(poolArr[i]);
            var winer = this.getMaxPointByPlayer(poolArr[i]['uidArr']);
            G.trace('winer');
            G.trace(winer);
            var winBet = poolArr[i].bet;
            var modWinBet = 0;//平分不了的奖金余数
            if(winer.length>1){
                //多个牌型一致的胜利者 则评分奖金
                winBet = winBet / winer.length;                
                modWinBet = winBet % winer.length;               
            }

            var modWinSite = 99; //分到奖金余数的座位（最靠近庄家）  
            var modWinUid ;  //分到奖金余数的人   
            var winUid;   //临时变量
            for(var bi=0;bi<winer.length;bi++){
                winUid = winer[bi];
                userWinBetArr[winUid] += winBet;
                //如果奖金无法平分，则余数给最靠近专家的胜利者         
                if(modWinBet>0){                        
                    var curModSite = this.players[winUid]['site'];
                    if( curModSite < this.bankerSite ){
                        curModSite += 10;
                    }           
                    if( curModSite<modWinSite){
                        modWinSite = curModSite;
                        modWInUid = winUid;
                    }         
                }
            }  
            //分到奖金余数的人
            if(modWinBet>0 && modWInUid){
                userWinBetArr[modWInUid] += winBet;
            }
        }

        G.trace('游戏结束 ');
        G.trace(userWinBetArr);

        //根据结算修改玩家手上筹码    
        for(var cuid in userWinBetArr){ 
            this.players[cuid].bet += userWinBetArr[cuid];
        }
    
        //发送结算信息给php处理
        var checkoutRS = {
            "mod"           :"room",
            "game"          :"dzPoker",
            "op"            :"checkout" ,
            "roomId"        : this.roomId,
            "result"        :{},                                   
            "underCards"    :G.cardToNum(this.underCardArr,'dzPoker'),
            "underCardsIndex":this.underCards_i+1,

            "compCardUserNum" : compCardUserNum,//最终参与比牌玩家人数
            //php
            "bankerSite"    :this.bankerSite
        }

        for(var pi in this.players){    

            // if(this.players[pi].standUp){
            //     continue;//中途已经站起的玩家不需要在发送结算信息
            // }

            G.trace('输赢情况 uid='+pi);

            G.trace('winBet='+userWinBetArr[pi] +'-'+ allBetList2[pi] +'='
                + (userWinBetArr[pi] - allBetList2[pi])
                );

            checkoutRS.result[pi] = {  
                "delayUseNum"   : this.players[pi].delayUseNum,//延时道具使用次数
                "cardType"      : this.playerCardType[pi],          
                "cardList"      : [],  //结算牌型 幸运牌型 
                "enterPool"     : this.players[pi].enterPool,//是否入池 1=入池 0=没有入池 入池率计算用
                "restBet"       : this.players[pi].bet,//结算后当前筹码
                "cards"         : G.cardToNum(this.players[pi].cards,'dzPoker'),//手牌
                "costBet"       : allBetList2[pi],
                "balance"       : userWinBetArr[pi] - allBetList2[pi] //输赢情况 <0输了 >0赢了
            };



           
            if(!this.players.out){//结算牌型 幸运牌型 
                checkoutRS.result[pi].cardList = G.cardToNum(this.checkoutCards[pi],'dzPoker');

                G.trace('幸运牌型 UID='+pi);
                G.trace( checkoutRS.result[pi].cardList );
            }

            //操作超时的告诉php 让他站起            
            if( this.players[pi].offlineTimeout ){//掉线超时就 结算后站起并退出房间
                checkoutRS.result[pi]['timeout'] = 1;
                checkoutRS.result[pi]['outRoom'] = 1;//站起后退出房间
            }else if(this.players[pi].opTimeoutNum>=2){//在线超时操作2次以上就 结算后站起
                checkoutRS.result[pi]['timeout'] = 1;
            }

            //delete(C['room_dzPoker_users'][pi]);//清除玩家缓存
            G.roomObj.delRoomUser(pi);
        }

        //delete(C['room_dzPoker'][this.roomId]);
        G.roomObj.delRoom(this.roomId);

        G.trace('checkoutRS');
        G.trace(checkoutRS);
        G.phpPost(checkoutRS,function(postRs){
            
            //php处理
            //筹码不足,自动站起
            //复位操作 players[uid].out = 0 
            //游戏过程已经outRoom的玩家
            //已弃牌的玩家是否已经退出房间
            //预约增加筹码的发送客户端
            //预备坐下的玩家 坐下处理
            //curPlayer.standUp = 1; 则表示该玩家已经在弃牌后退出牌局,php直接删除掉就好,不需要做筹码操作
            //所有玩家缓存清楚
            //C['room_dzPoker_users'][uid] 
            //
            if(!postRs){                            
                console.error('非法格式:'+postRs);  
                self.toClient({                     
                     "op"    : "opError",
                     "msg"   : "feifageshi"
                });         
                return;
            }   
            if(postRs.code!=200){                            
                console.error('code:'+postRs.code);
                self.toClient({                     
                     "op"    : "opError",
                     "msg"   : postRs.msg
                });
                return;
            }   
            //checkoutRS['players'] = postRs.data.players;            
            var curBet = 0;
            var curBanlace = 0;
            for(var ui in checkoutRS.result){
                if(postRs.data.players[ui]){
                    curBet = postRs.data.players[ui].bet;
                    curBalance = postRs.data.players[ui].balance;
                }else if(postRs.data.standUpPlayers[ui]){
                    curBet = postRs.data.standUpPlayers[ui].bet;
                    curBalance = postRs.data.standUpPlayers[ui].balance;
                }else{
                    continue;
                }                
                checkoutRS.result[ui].restBet = curBet;
                checkoutRS.result[ui].balance = curBalance;
            }
            
            //筹码不足自动站起的消息必须发送给所有玩家
            var t ;
            for(var upUid in postRs.data.standUpPlayers){               
                G.trace('掉线或筹码不足自动站起的消息必须发送给所有玩家 standUpPlayer='+upUid);
                //已经中途站起的玩家不需要在站起
                if( self.players[upUid].standUp ){
                    continue;
                }
                //站起原因        
                if( self.players[upUid].offlineTimeout ){//掉线超时就 结算后站起并退出房间
                    t = 'offline';//掉线超时 
                }else if(self.players[upUid].opTimeoutNum>=2){//在线超时操作2次以上就 结算后站起
                    t = 'opTimeout';//操作超时
                }else{
                    t = 'betLess';//筹码不足
                }
                G.trace('站起原因 '+t);

                self.toClient({
                    "uid":upUid,
                    "op":"standUp",
                    "type" : t
                });    
            }

               
            delete(checkoutRS['bankerSite']);
            self.toClient(checkoutRS);

            if(postRs.data.roomOver){
                //如果房间已经结束则告诉客户端
                self.toClient({                   
                    "op":"roomOver"
                });    
            }
           
            //自动开始下一局
            G.trace('自动开始下一局');
            G.trace(CFG.game_dzPoker.nextStartTimeout);
            G.roomObj.nextStartByRoomId(self.roomId,CFG.game_dzPoker.nextStartTimeout);
        
            
        });
        
        return true;
    }//checkout

    //玩家uid列表中匹配出胜利者
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
                //离开的玩家不需要发送 
                if(this.players[i].standUp){
                    continue;
                }
                uidArr.push(i);
            }
            //旁观者也要发送消息
            for(var j in this.upPlayers){
         
                uidArr.push(j);
            }
        }else if(!Array.isArray(uidArr)){
            uidArr = [uidArr];
        }

        data['mod'] = 'game_dzPoker';
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
//牌组是否是顺子
function isSeqCards(cards){

    cards.sort(sortByObj('n')); //从小到大排序

    var si = 0;
    var maxSi = cards.length-1;

    var seqCards = [ cards[si] ];
    while( si<maxSi){    //循环判断是否是顺子

        if( cards[si+1].n - cards[si].n ===1  ){ //是顺子 则加入顺子列表          
            seqCards.push(cards[si+1]);
        }else if( cards[si+1].n !== cards[si].n ){//如果前后不是同个点数 则之前不是顺子 清空顺子列表
                 
            if(seqCards.length>=5){//已经有顺子了               
                break;
            }
            seqCards = [];
            seqCards.push(cards[si+1]);
        }
        si++;
    }

    if( seqCards.length>=5){
        while(seqCards.lenght>5){
            seqCards.shift();
        }
        return {'seq':true,'cards':seqCards};
    }else{
        while(cards.lenght>5){
            cards.shift();
        }
        return {'seq':false,'cards':cards};
    }
}


function showCardTxt(cards){

    
    G.trace('所有牌------------- BEGIN');
    var k;
    for(var i in cards){
        k = cards[i].n+'-'+ cards[i].c;
        G.trace( cardCFG[k] );
    }
    G.trace('所有牌------------- END');
    
}

var cardCFG = {
        "12-0" : 'A',
        "12-1" : 'A',
        "12-2" : 'A',
        "12-3" : 'A',
        "0-0" : 2,
        "0-1" : 2,
        "0-2" : 2,
        "0-3" : 2,
        "1-0" : 3,
        "1-1" : 3,
        "1-2" : 3,
        "1-3" : 3,
        "2-0" : 4,
        "2-1" : 4,
        "2-2" : 4,
        "2-3" : 4,
        "3-0" : 5,
        "3-1" : 5,
        "3-2" : 5,
        "3-3" : 5,
        "4-0" : 6,
        "4-1" : 6,
        "4-2" : 6,
        "4-3" : 6,
        "5-0" : 7,
        "5-1" : 7,
        "5-2" : 7,
        "5-3" : 7,
        "6-0" : 8,
        "6-1" : 8,
        "6-2" : 8,
        "6-3" : 8,
        "7-0" : 9,
        "7-1" : 9,
        "7-2" : 9,
        "7-3" : 9,
        "8-0" : 10,
        "8-1" : 10,
        "8-2" : 10,
        "8-3" : 10,
        "9-0" : 'J',
        "9-1" : 'J',
        "9-2" : 'J',
        "9-3" : 'J',
        "10-0" :'Q',
        "10-1" :'Q',
        "10-2" :'Q',
        "10-3" :'Q',
        "11-0" :'K',
        "11-1" :'K',
        "11-2" :'K',
        "11-3" :'K',
        "13_ghost_s":53,
        "14_ghost_b":54
    };