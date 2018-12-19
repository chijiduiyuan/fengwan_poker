/*
 * 配置主文件
 *
 * author cxf
 * versions 2016-06-10
 */

module.exports = {
	"debug"	:false,
    //非法言论过滤
    "fitr" 	        : require("../../config/other/fitr.json"),
    //客户端提示代码配置
    "code" 	        : require("../../config/other/code.json"),
    //常量配置
    //"const" 	    : require("../../config/other/const.json"),  
  //  "const"         : App.const,  
    // "const"         : {
    //     'phpUrl' : 'http://192.168.1.17/poker/node.php'
    // },  

    //卡牌配置 卡牌json 转换成数值
    "card":{
        "def"       : require("../../config/card/def.json"),
        "dzPoker"   : require("../../config/card/dzPoker.json"),
        "cowWater"  : require("../../config/card/cowWater.json"),
        "cowWater_numToObj"  : require("../../config/card/cowWater_numToObj.json")
    },
    //游戏-德州扑克 配置
    "game_dzPoker"      : {
        "playerNum":9,
        "siteDowStartTimeout" : 2*1000, //坐下后游戏开始 延迟 单位 毫秒
        //"timeoutRound" : 32,//每轮玩家操作超时
        "nextStartTimeout" : 15*1000 //结算后下一局游戏开始 延迟 单位 毫秒
    },
    
    //游戏-牛加水 配置
    "game_cowWater"      : {
        "siteDowStartTimeout" : 2*1000, //坐下后游戏开始 延迟 单位 毫秒
        "nextStartTimeout" : 15*1000, //结算后下一局游戏开始 延迟 单位 毫秒

        "callBankerTimeout_SG" : 10, //三公叫庄超时时间 单位秒                        
        "showCardTimeout_SG": 20, //三公搓牌超时时间 单位秒
        //"checkoutTimeout_SG" : 15, //15三公结算后牛牛标庄开始之间的暂停时间 单位秒

        "callBankerTimeout_COW": 12, //牛牛叫庄超时时间 单位秒
        "putCardTimeout_COW": 20, //牛牛搓牌,摆牌 总超时时间 单位秒

        //倍率设置 - 三公
        "odds_SG" : {
            "THREE_CARD"    :5, //三条
            "JQK_CARD"      :3, //任意三张JQK
            "TEN_CARD"      :2, //十点 三张相加为10
            "NINE_CARD"     :2, //九点 三张相加为9
            "OTHER_CARD"    :1 //其他点数
        },
        //倍率设置 - 牛牛
        // "odds_COW" : {
        //     "FIVE_CARD"         :7, //五公 5张JQK
        //     "COW_NDG_CARD"      :5, //牛冬菇 3张JQK+黑桃A+其他任意牌,有牛+A+JQK任意一张
        //     "COW_BABY_TOW_CARD" :4, //牛宝宝+双A（x4） 有牛+2张A
        //     "COW_BABY_CARD"     :3, //牛宝宝（x3） 有牛+1对
        //     "COWCOW_CARD"       :2, //牛牛 有牛+10点
        //     "COW_CARD"          :1, //牛 有牛+点
        //     "SINGLE_CARD"       :1 //没牛        
        // },
        "odds_COW" : {
            "COW_FIVE_SMALL"    :10, //五小牛
            "COW_BOMB"          :9, //炸弹
            "COW_GOLD"          :8, // 金牛
            "COW_SILVER"        :7, // 银牛
            "COW_BIG_BULL"      :5, //牛牛 有牛+10点
            "COW_BULL_9"        :4, //牛9 有牛+点
            "COW_BULL_8"        :3, //牛8
            "COW_BULL_7"        :2,
            "COW_BULL_6"        :1,
            "COW_BULL_5"        :1,
            "COW_BULL_4"        :1,
            "COW_BULL_3"        :1,
            "COW_BULL_2"        :1,
            "COW_BULL_1"        :1,  
            "COW_NO_BULL"       :1 //没牛           
        },
        "callBankerOdds_SG" : [0,1,2,3],//三公 叫庄倍率 0表示没有叫庄资格
        "callBankerOdds_COW": [0,1,2,3] //牛牛 叫庄倍率
    },
    "game_thriDucal" : {
        "siteDowStartTimeout" : 2*1000, //坐下后游戏开始 延迟 单位 毫秒
        "nextStartTimeout" : 15*1000, //结算后下一局游戏开始 延迟 单位 毫秒

        "callBankerTimeout_SG" : 10, //三公叫庄超时时间 单位秒                        
        "showCardTimeout_SG": 20, //三公搓牌超时时间 单位秒
        //"checkoutTimeout_SG" : 15, //15三公结算后牛牛标庄开始之间的暂停时间 单位秒

        //倍率设置 - 三公
        "odds_SG" : {
            "THREE_CARD"    :5, //三条
            "JQK_CARD"      :3, //任意三张JQK
            "TEN_CARD"      :2, //十点 三张相加为10
            "NINE_CARD"     :2, //九点 三张相加为9
            "OTHER_CARD"    :1 //其他点数
        },
        "callBankerOdds_SG" : [0,1,2,3],//三公 叫庄倍率 0表示没有叫庄资格
    },
    "game_cowcow" : {
        "siteDowStartTimeout" : 2*1000, //坐下后游戏开始 延迟 单位 毫秒
        "nextStartTimeout" : 15*1000, //结算后下一局游戏开始 延迟 单位 毫秒

        "callBankerTimeout_COW": 12, //牛牛叫庄超时时间 单位秒
        "putCardTimeout_COW": 60, //牛牛搓牌,摆牌 总超时时间 单位秒

        "odds_COW" : {
            "COW_FIVE_SMALL"    :10, //五小牛
            "COW_BOMB"          :9, //炸弹
            "COW_GOLD"          :8, // 金牛
            "COW_SILVER"        :7, // 银牛
            "COW_BIG_BULL"      :5, //牛牛 有牛+10点
            "COW_BULL_9"        :4, //牛9 有牛+点
            "COW_BULL_8"        :3, //牛8
            "COW_BULL_7"        :2,
            "COW_BULL_6"        :1,
            "COW_BULL_5"        :1,
            "COW_BULL_4"        :1,
            "COW_BULL_3"        :1,
            "COW_BULL_2"        :1,
            "COW_BULL_1"        :1,  
            "COW_NO_BULL"       :1 //没牛           
        },
        "callBankerOdds_COW": [0,1,2,3] //牛牛 叫庄倍率
    },



}

