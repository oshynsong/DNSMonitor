/** 
 *DNS Server Monitor
 *@author OshynSong
 *@time   2014-12
 */
 
(function(){

	$('#map').css({'height': window.innerHeight+'px',margin:'0px'});
	var width =  $('#map').width(),
        height = $('#map').height();
		
	var projection = d3.geo.mercator().center([110, 42]);  //投影函数
	projection.scale(700);   //首次缩放
	var path = d3.geo.path().projection(projection);   //绘制路径，不能传入缩放过的投影函数
	//重绘函数使用
	var t = projection.translate();  //平移
    var s = projection.scale();   //放缩
	
	var svg = d3.select("#map").append("svg")
                .attr("width", width)
                .attr("height", height)
				.call(d3.behavior.zoom().on("zoom", reDraw));
	var china = svg.append("svg:g").attr("id", "china");
	
	var axes = svg.append("svg:g").attr("id", "axes");
    var xAxis = axes.append("svg:line")
        .attr("x1", t[0])
        .attr("y1", 0)
        .attr("x2", t[0])
        .attr("y2", height);
    var yAxis = axes.append("svg:line")
        .attr("x1", 0)
        .attr("y1", t[1])
        .attr("x2", width)
        .attr("y2", t[1]);
	
	
	var chinaMapData = 'data/china.json';
	
	var WHOLE_DNS = [];
	var dnsBuf = {
	 // "215.33.112.111":{"x":122.24444,"y":47.29444, "addr":"放放风"},
	 // "165.33.23.1":{y:39.91488908,x:116.40387397,addr:"北京市中科院"}
	};
	var links = [
	//	[{y:39.91488908,x:116.40387397,addr:"北京市中科院"}, {"x":122.24444,"y":47.29444, "addr":"放放风"}],
		/*[{"x":116.09167,"y":39.40278}, {"x":16.39564504, "y":34.92998578}],
		[{"y":40.4080231,"x":116.6823389}, {"x":124.5,"y":31.33333}],
		[{"x":124.5,"y":31.33333}, {"x":122.24444,"y":47.29444}],
		[{y:39.91488908,x:116.40387397},{y:39.9224702,x:-104.9834234}],*/
	];
	
	
	function drawNode(nodes, proj){
		
		 svg.selectAll("circle")
			.data(d3.entries(nodes))
			.enter()
			.append("circle")
			.classed('node', true)
			.attr("cx", function(d) {
					return proj([d.value.x, d.value.y])[0];
			})
			.attr("cy", function(d) {
					return proj([d.value.x, d.value.y])[1];
			})
			.attr("r", 3.5)
			.style("fill", "#AFECF4")
			.style("opacity", 0.75);
	//	svg.append('text').attr({x:100,y:100}).style("color","#fff").html('3234343');
	}
	
	function drawStream(links, proj, delay){
		
		var d3line = d3.svg.line()
                    .x(function(d){return proj([d.x, d.y])[0];})
                    .y(function(d){return proj([d.x, d.y])[1];})
                    .interpolate("linear");
		for (var i = 0; i < links.length; i++){
			var node1 = links[i][0];
			var node2 = links[i][1];
			if (delay){
				svg.append("path")
				.classed('dnsLinks', true)
				.attr("d", d3line([node1, node1]))
				.style("stroke-width", 1.5)
				.style("stroke", "#fff")
				.style("fill", "none")
				.style('stroke-opacity', 1)
				
				.transition()
				.duration(200)
				.ease('linear')
				
				.attr("d", d3line([node1, node2]));
				
			}else{
				svg.append("path")
				.classed('dnsLinks', true)
				.attr("d", d3line([node1, node2]))
				.style("stroke-width", 1.5)
				.style("stroke", "#fff")
				.style("fill", "none")
				.style('stroke-opacity', 1);
			}
		}
	}
	
	function drawMap(jsonData){
		d3.json(jsonData, function(json) {
		/*china.selectAll("path")
			  .data(json.features)
			.enter().append("svg:path")
			  .attr("d", path);*/
		
			china.selectAll("path")
			 .data(json.features)
			 .enter()
			 .append("svg:path")
			 .attr("d", path)
			 .style("fill","#2C2C43")//steelblue
			 .style("stroke-width", "1")
			 .style("stroke", "#666689");
		});
	}
	
	
	
	
	var wsserver = "ws://localhost:8888";
	
	var DNSMonitor = function (btnStart, btnEnd, btnFreq){
		var ws;
		
		this.init = function(){
			btnStart.onclick = btnStartClick;
			btnEnd.onclick = btnEndClick;
			btnFreq.onclick = btnFreqClick;
		};
		
		var btnStartClick = function(){
			var infoBox = $('#infoBox');
			infoBox.children('div').remove();
			
			ws = new WebSocket(wsserver);			
			ws.onopen = function(){
				console.log("握手成功，打开socket连接了。。。");
				ws.send('d');
			};
			ws.onclose = function(){
				console.log("断开socket连接了。。。");
			};
			ws.onerror = function(e){
				console.log("ERROR:" + e.data);
			};
			
			ws.onmessage = function(e){
				var msg = e.data;
				var m = msg.split('#');
				//console.log('message: ' + msg);
				//console.log(m);return;
				var info = eval('('+ m[1] + ')');
				console.log(info);				
				infoBox.append('<div class="line">'+msg+'</div>');
				infoBox.scrollTo(infoBox.scrollTop() + infoBox.height());
				
				var index = 0;
				var tmp = [];
				for (var i in info){
					dnsBuf[i] = info[i];
					var a = {i: info[i]};
					tmp.push(info[i]);
				}
				drawNode(dnsBuf, projection);
				
				for (var i = 0; i < tmp.length-1; i++){
					links.push([tmp[i],tmp[i+1]]);
					drawStream([[tmp[i],tmp[i+1]]], projection, true);
				}
				
				ws.send('d');
			};
			
		};
				
		var btnEndClick = function() {
			ws ? ws.close() : ws;
			ws = {};
		}
		var btnFreqClick = function(){
			var infoBox = $('#infoBox');
			infoBox.children('div').remove();
			//if (!ws) ws.close();
			
			ws = new WebSocket(wsserver);			
			ws.onopen = function(){
				console.log("握手成功，打开socket连接了。。。");
				ws.send('1');
			};
			ws.onclose = function(){
				console.log("断开socket连接了。。。");
			};
			ws.onerror = function(e){
				console.log("ERROR:" + e.data);
			};
			
			dnsBuf = {};
			links = [];
			ws.onmessage = function(e){
				var msg = e.data;
				//console.log('message: ' + msg);
				var info = eval('('+ msg + ')');
				console.log(info);				
				infoBox.append('<div class="line">'+msg+'</div>');
				infoBox.scrollTo(infoBox.scrollTop() + infoBox.height());
				
				var index = 0;
				var tmp = [];
				for (var i in info){
					dnsBuf[i] = info[i];
					var a = {i: info[i]};
					tmp.push(info[i]);
				}
				drawNode(dnsBuf, projection);
				
				for (var i = 0; i < tmp.length-1; i++){
					links.push([tmp[i],tmp[i+1]]);
					drawStream([[tmp[i],tmp[i+1]]], projection, true);
				}
			};
		}
	};
	(new DNSMonitor(document.getElementById("start"),
					document.getElementById("end"),
					document.getElementById("freq"))).init();
	
	function reDraw () {
		var tx = t[0] * d3.event.scale + d3.event.translate[0];
		var ty = t[1] * d3.event.scale + d3.event.translate[1];
		projection.translate([tx, ty]);
		
		projection.scale(s * d3.event.scale);

		// redraw the map
		china.selectAll("path").attr("d", path);

		// redraw the x axis
		xAxis.attr("x1", tx).attr("x2", tx);

		// redraw the y axis
		yAxis.attr("y1", ty).attr("y2", ty);

		$('#map').find('.node').remove();
		$('#map').find('.dnsLinks').remove();
		drawNode(dnsBuf, projection);
		drawStream(links, projection, false);	  
	}
	
	
	
	;!function (){
		drawMap(chinaMapData);
		setTimeout(function (){
			drawNode(dnsBuf, projection);
			setTimeout(function(){
				drawStream(links, projection, true);
			}, 1000);
		}, 1000);
	}();
	
})();