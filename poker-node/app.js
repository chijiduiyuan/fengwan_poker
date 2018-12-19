/**
 * 主入口模块
 *
 * author cxf
 * date 2017-06-7
 */

var pomelo = require('pomelo');
var routeUtil = require('./app/utils/routeUtil');
var path = require('path');
/**
 * Init app for client.
 */
//全局对象
global.App    = pomelo.createApp();

App.set('name', 'poker');


global.CFG  = require('./app/utils/initCFG');     //配置文件初始化

App.loadConfig('const', path.resolve(__dirname,'./config/other/const.json') );

CFG.const = App.get('const');

global.G    = require('./app/utils/fun');   //全局函数、对象


G.frontendId = 'connector-server-1';  //默认前端服务器
G.roomObj = 'def';
//缓存对象
global.C = {
	'roomByServer' : { //前端服务器中用于存储 roomId 对应的 serverId 用于路由计算
		'dzPoker'  : {},
		'cowWater' : {},
		'thriDucal': {},
		'cowcow': {}
	},

	'seqNum':0,//游戏历史实例数 累加
	'timeroutArr':{},//定时启动游戏 锁
	'roomPlayRequesting':{},//启动游戏过程 锁
	'room_dzPoker' : {},//德州 游戏中房间信息 
	'room_dzPoker_users' : {},//德州 玩家在游戏中
	'room_cowWater' : {},//牛加水 游戏中房间信息 
	'room_cowWater_users' : {},//牛加水 玩家在游戏中
	'room_thriDucal' : {},//三公 游戏中房间信息 
	'room_thriDucal_users' : {},//三公 玩家在游戏中
	'room_cowcow' : {},//牛牛 游戏中房间信息 
	'room_cowcow_users' : {}//牛牛 玩家在游戏中
};



// app configuration
App.configure('production|development', 'connector', function(){

	App.set('connectorConfig',
	{
		connector : pomelo.connectors.hybridconnector,
		heartbeat : 15,//3
		//heartbeat : 60,
		useDict : true,
		useProtobuf : true
	});

	App.route('game_dzPoker', routeUtil.game_dzPoker);
	App.route('game_cowWater', routeUtil.game_cowWater);
	App.route('game_thriDucal', routeUtil.game_thriDucal);
	App.route('game_cowcow', routeUtil.game_cowcow);
});


// configure for global
App.configure('production|development', function() {
	App.before(pomelo.filters.toobusy());

	App.filter(pomelo.filters.timeout());
});

//德州
App.configure('production|development', 'game_dzPoker', function(){
	G.roomObj = require('./app/classes/Room_dzPoker.class');
	G.trace('初始化德州 进程');
	
});
//牛加水
App.configure('production|development', 'game_cowWater', function(){
	G.roomObj = require('./app/classes/Room_cowWater.class');
	G.trace('初始化牛加水 进程');
});
// 三公
App.configure('production|development', 'game_thriDucal', function(){
	G.roomObj = require('./app/classes/Room_thriDucal.class');
	G.trace('初始化三公 进程');
});
// 牛牛
App.configure('production|development', 'game_cowcow', function(){
	G.roomObj = require('./app/classes/Room_cowcow.class');
	G.trace('初始化牛牛 进程');
});


// start app
App.start();

process.on('uncaughtException', function (err) {
	console.error(' Caught exception: ' + err.stack);
});
