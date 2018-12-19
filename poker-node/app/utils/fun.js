/*
 * 公用函数库
 *
 *  @author cxf
 * versions 2016-04-27
 */
var obj = module.exports;
obj.trace = function(obj){
    if( CFG['debug']){
        //debug模式直接控制台输出
        console.log(obj);
    }else{
        //写入日志文件
    }
}

/***
 *   取频道
 *
 */
obj.getCH_obj = function(){
    return App.get('channelService');
}
// //发送给客户端信息
// obj.toClient = function(data,uidArr,callBack){
//     var channelObj = G.getCH_obj();
//     if(!callBack){
//         var callback = function(){};
//     }

//     if(!Array.isArray(uidArr)){
//         uidArr = [uidArr];
//     }

//     for( var i in uidArr){
//         channelObj.pushMessageByUids('onData', data, [{'uid':uidArr[i], 'sid':G.frontendId}], function(){
//             callBack();
//         });
//     }

// }//toClient


/***
 *   sql字符串转化 防止sql注入
 *
 */
obj.sqlStr = function(str){
    //str=str.replace("'","\\'");
    //str=str.replace('\"','\\"');
    return str;
}
/***
 * 取随机数
 *
 */
obj.rand = function(Min,Max){
    var Rand = Math.random();
    //  if(!Min&&!Max){
    //     return Rand;
    //  }
    var Range = Max - Min;
    return(Min + Math.round(Rand * Range));
}
/***
 * 错误日志
 *
 */
obj.log = function(uid,level,code,title){

    var uid   = uid||0;
    var title = title|| '';
    title += '='+ CFG['code'][code];

    var upInfo = {
        'uid':uid,
        'level':level,
        'code':code,
        'title':title,
        'create_time': this.time()
    };

    DB.insert('log_bug',upInfo,function(data){

    });
}
/***
 * 判断对象是否是空
 * @param obj
 * @return bool true/false
 */
obj.objIsEmpty = function(obj){

    var empty = true;
    for(var i in obj){
        empty = false;
        break;
    }
    return empty;
}

/***
 * 判断参数是否在数组中
 * @param needle 要查询的值
 * @param array 查询对象数组
 * @param bool 是否返回查询到的数据key
 */
obj.inArray = function(needle,array,bool){

    var bool = (typeof(bool) == "undefined"?false:bool);

    if(typeof needle=="string"||typeof needle=="number"){
        var len=array.length;
        for(var i=0;i<len;i++){
            if(needle===array[i]){
                if(bool){
                    return i;
                }
                return true;
            }
        }
        return false;
    }
}

/***
 * 数组乱序,
 * @param o array 对象数组
 * @return 重新随机排列后的数组
 */
obj.randomAry = function(o){
    for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
}


/***
 * 取数组随机数
 * @param ary array 对象数组
 * @return 随机数组
 */
obj.arrayRand = function(ary) {
	return ary[Math.floor(Math.random()*ary.length)];
}


/***
 * 取数组中最大值
 * @param ary array 对象数组
 * @return 最大值
 */
obj.arrayMax = function(ary) {
	return Math.max.apply(Math, ary);
}


/***
 * 取数组中最小值
 * @param ary array 对象数组
 * @return 最小值
 */
obj.arrayMin = function(ary) {
	return Math.min.apply(Math, ary);
}

/***
 * 克隆对象
 * @param myObj 对象
 * @return 克隆后的新对象
 */
 /*
obj.clone = function(myObj){
    if(typeof(myObj) != 'object') return myObj;
    if(myObj == null) return myObj;
    var myNewObj = new Object();
    for(var i in myObj) {
        myNewObj[i] = this.clone(myObj[i]);
    }
    return myNewObj;
}
*/
obj.clone = function(obj){
    var o;
    if(typeof obj == "object"){
        if(obj === null){
            o = null;
        }else{
            if(obj instanceof Array){
                o = [];
                for(var i = 0, len = obj.length; i < len; i++){
                    o.push(this.clone(obj[i]));
                }
            }else{
                o = {};
                for(var k in obj){
                    o[k] = this.clone(obj[k]);
                }
            }
        }
    }else{
        o = obj;
    }
    return o;
}
/**
 *  继承
 *  @Child 子类
 *  @Parent 父类
 **/
obj.extend = function( Child,Parent ){
    var p = Parent.prototype;
    var c = Child.prototype;
    for(var i in p){
        c[i] = p[i];
    }
    c.uber =  p;
}

/**
 *  取对象类型
 *  @objClass 子类
 **/
obj.typeof = function( objClass ){
    if ( objClass != undefined && objClass.constructor )
    {
        var strFun = objClass.constructor.toString();
        var className = strFun.substr(0, strFun.indexOf('('));
        className = className.replace('function', '');
        return className.replace(/(^\s*)|(\s*$)/ig, '');
    }
    return typeof(objClass);
}

/***
 *  非法字符判断:昵称创建,聊天
 *
 */
obj.checkStr = function( str ){

    for(var i in CFG['fitr']){
        if( str.indexOf( CFG['fitr'][i] )>-1 ){
            return false;
        }
    }
    return true;
}

/**
 * 取概率
 * @param reward array 物品数组
 * @return 返回命中后的物品数组
 */
obj.getRateItem = function( reward ){

    var num = reward.length*1;
    var pro,curPro;  //概率
    var min = 0;
    var max  = 10000;
    var rsAry=[];
    G.trace(reward);
    G.trace('掉落概率计算');
    for(var i= 0;i<num;i++){
        //掉落概率计算
        curPro = reward[i]['rate']*max;
        pro = G.rand(min,max) ;

        G.trace('rate:'+reward[i]['rate']+' curPro'+curPro+'  pro'+pro);

        if(curPro>=pro){ //概率命中
            rsAry.push( reward[i] );
        }
    }
    return rsAry;
} //getRateItem

/**
 * 概率是否中
 * @param num float 概率   0-1
 * @return 返回true=命中 false=没有命中
 */
obj.isRateOk = function( num ){
    var min = 0;
    var max = 10000;
    curPro = num*max;
    pro = G.rand(min,max) ;
    if(curPro>=pro){ //概率命中
        return true;
    }else{
        return false;
    }
}

/**
 * 是否是整数
 * @param num float 概率   0-1
 * @return 返回true=命中 false=没有命中
 */
obj.isInt = function(obj){
    return typeof obj === 'number' && obj%1 === 0;
}

/***
 *  去除字符2端空格
 *
 */
obj.trim = function(str){
    return str.replace(/(^\s*)|(\s*$)/g, "");
}

/***
 * 获取当前时间戳
 *
 */
obj.time = function(){
    return Math.round(new Date().getTime()/1000);
}

/**
* 获取当前毫秒
*
*/
obj.millisecond = function(){
    return new Date().getTime();
}
/**
 * 获取上一个月
 *
 * @date 格式为yyyy-mm-dd的日期，如：2014-01-25
 * @测试 alert(getPreMonth("2014-01-25"));
 */
obj.getPreMonth = function( date ){
    var arr = date.split('-');
    var year = arr[0]; //获取当前日期的年份
    var month = arr[1]; //获取当前日期的月份
    var day = arr[2]; //获取当前日期的日
    var days = new Date(year, month, 0);
    days = days.getDate(); //获取当前日期中月的天数
    var year2 = year;
    var month2 = parseInt(month) - 1;
    if (month2 == 0) {
        year2 = parseInt(year2) - 1;
        month2 = 12;
    }
    var day2 = day;
    var days2 = new Date(year2, month2, 0);
    days2 = days2.getDate();
    if (day2 > days2) {
        day2 = days2;
    }
    if (month2 < 10) {
        month2 = '0' + month2;
    }
    var t2 = year2 + '-' + month2 + '-' + day2;
    return t2;
}

/**
 * 获取下一个月
 *
 * @date 格式为yyyy-mm-dd的日期，如：2014-01-25
 * @测试 alert(getNextMonth("2014-12-25"));
 */
obj.getNextMonth = function( date ){
    var arr = date.split('-');
    var year = arr[0]; //获取当前日期的年份
    var month = arr[1]; //获取当前日期的月份
    var day = arr[2]; //获取当前日期的日
    var days = new Date(year, month, 0);
    days = days.getDate(); //获取当前日期中的月的天数
    var year2 = year;
    var month2 = parseInt(month) + 1;
    if (month2 == 13) {
        year2 = parseInt(year2) + 1;
        month2 = 1;
    }
    var day2 = day;
    var days2 = new Date(year2, month2, 0);
    days2 = days2.getDate();
    if (day2 > days2) {
        day2 = days2;
    }
    if (month2 < 10) {
        month2 = '0' + month2;
    }

    var t2 = year2 + '-' + month2 + '-' + day2;
    return t2;

}

/**
 * 根据日期转为时间戳
 *
 * @str_time 格式为yyyy-mm-dd H:M:S的日期，如：2014-12-25 23:59:59
 * @测试 alert(getNextMonth("2014-12-25 23:59:59"));
 */
obj.getTimeByString = function( str_time ){
    var new_str = str_time.replace(/:/g,'-');
    new_str = new_str.replace(/ /g,'-');
    var arr = new_str.split("-");
    var datum = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return strtotime = datum.getTime()/1000;
}

/**
 * 根据时间戳转为日期
 *
 * @nS 1334822400转为格式为yyyy-mm-dd H:M:S的日期，如：2014-12-25 23:59:59
 * @
 */
obj.timeToDate = function( nS ,format ){
    var now 	= new Date(nS*1000);
    var year	= now.getFullYear();
    var month	= now.getMonth()+1;
    var date	= now.getDate();
    var hour	= now.getHours();
    var minute	= now.getMinutes();
    var second	= now.getSeconds();
    if(!format){
        format = '年月日';
    }
    switch(format){
        case '年月日':
            return year+'年'+month+'月'+date+'日';
            break;
        default:
            return year+'年'+month+'月'+date+'日';
            break;
    }
    //return   year+"-"+month+"-"+date+" "+hour+":"+minute+":"+second;
}

/**
 * 根据时间(秒)转为时间
 *
 * @nS 22400转为格式为H:M:S的日期，如：23:59:59
 * @
 */
obj.timeFormat = function( nS,format ){
    //时
    var h		= Math.floor(nS/3600);
    //分
    var m		= Math.floor( (nS-h*3600)/60 );
    //秒
    var s		= nS-h*3600-m*60;

    if( !format ){
        format = 'h小时m分s秒';
    }


    switch(format){
        case 's':
            return nS;
            break;
        default:
            return h+':'+m+':'+s;
            break;
    }
}

/**
 * 判断是否当天(用来做每日回复次数的判断)
 * @param oldTime int 之前记录时间
 * @return bool Boolean 返回是否是当天   true=当天 flase=不是当天
 */
obj.isCurDay = function( oldTime ){
    var now         = G.time();
    var oneDay      = 3600*24;//一天
    var rs = true;

    if(!oldTime){
        rs = false;
    }else{
        if( now-oldTime>oneDay ){ //时间差大于1天
            rs = false;
        }else{ //时间差一天内则判断 日期是否一致
            var curDay  = new Date().getDate();
            var oldDay  = new Date( oldTime*1000 ).getDate();
            if( curDay!=oldDay ){
                rs = false;
            }
        }
    }
    return rs;
}

/*
 *   MD5方法
 */
obj.md5 = function(str) {
    //return str;
    str += '';
    return require('crypto').createHash('md5').update(str).digest('hex');
}
/*
 *   生成唯一性id
 */
obj.newGuid = function(){
    var guid = "";
    for (var i = 1; i <= 32; i++){
        var n = Math.floor(Math.random()*16.0).toString(16);
        guid +=   n;
        if((i==8)||(i==12)||(i==16)||(i==20))
            guid += "-";
    }
    return G.md5(guid+ G.time());
}

/**
 *  同步session里的info为最新数据
 *  @session 当前session对象
 *  @obj 要更新的json对象
 **/
obj.updateSession = function(session,obj){

    var userInfo = session.get("userInfo");

    for(var p in obj){
        userInfo[p] = obj[p];
    }

    session.set('userInfo',userInfo);
    G.trace('更新session');
    G.trace(userInfo);
    session.push('userInfo', function(err) {
        if(err) {
            console.error('set uid for session service failed! error is : %j', err.stack);
        }
    });
}//updateSession

/**
 *  请求PHP服务器 POST模式
 *  @param {json} postData post参数
 *  @param {function} callback 回调函数 
 **/
obj.phpPost = function(postData,callback,url){
    
    var showDebug = postData.op!='getMsg' && postData.op!='gameProcessNum';

    var postUrl;
    if(url){
        postUrl = url;
    }else{
        postUrl = CFG.const.phpUrl+'?_t='+Math.random();
    }
    postUrl += '?_t='+Math.random();

    if(showDebug){
        G.trace('phpPost');
        G.trace(postData);
        G.trace('postUrl:');
        G.trace(postUrl);
    }
   

    G.request.post({
        url: postUrl,             
        form: postData,
        //formData:postData,
        timeout: 5000 //超时时间5秒
    // G.request({
    //     method : 'POST',
    //     url: postUrl,     
    //     json: true,   
    //    // body: postData,     
    //     formData : postData,
    //     timeout: 5000 //超时时间5秒
    }, function(error, response, body) {
        if (!error && response.statusCode == 200) {
            //json解析异常处理   
            //body = '{ code: 200, data: { roomInfo: null } }';         
            
            try {  
                //var jsonRS = body;    
                var jsonRS = JSON.parse(body);            
            }catch(e){
                //php请求失败 写入日志 json解析异常
                console.error('json解析异常');
                console.error(e);
                G.trace('body内容:');
                G.trace(body);
                callback(false);
                return;
            }
            if(showDebug){
                G.trace('php请求结束');
                G.trace(jsonRS);
            }
            callback(jsonRS);            
        }else{//php请求失败 地址不存在 写入日志
            console.error('php请求失败 或者地址不存在 '); 
            //console.error(response.statusCode); 
            console.error(error);            
            callback(false);
        }
    });

}//phpPost

/**
 *  卡牌json 转换成数值
 *  @param {json/array} cards 卡牌对象或者数组
 *  @param {function} callback 回调函数 
 **/
obj.cardToNum = function(cards,cardType){
    //var cardType = cardType||'def';
    var cardCFG = CFG.card[cardType];

    var cardsArr = [];
    if(Array.isArray(cards)){
        cardsArr = cards;
    }else{
        cardsArr.push(cards);
    }
    var cardNumArr = [];
    for(var i=0;i<cardsArr.length;i++){

        cardNumArr.push(cardCFG[ cardsArr[i].n +'-'+ cardsArr[i].c]);
    }

    if(Array.isArray(cards)){
        return cardNumArr;
    }else{
        return cardNumArr[0];
    }
}//cardToNum



/***
 * 公共类实例
 *
 */
obj.async      = require('../libs/async/async');  //工作流
obj.request    = require('request'); // curl
