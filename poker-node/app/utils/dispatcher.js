/*
 * 负载均衡算法
 *
 * author cxf
 * versions 2016-06-10
 */

module.exports.dispatch = function(idx, connectors) {
	var index = Number(idx) % connectors.length;

	G.trace('dispatch idx='+index+' id='+connectors[index].id);
	return connectors[index];
};
