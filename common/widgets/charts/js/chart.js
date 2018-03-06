/**
 * 多个数据一个bar 显示
 * @param {type} win
 * @param {type} $
 * @returns {undefined}
 */
(function(win,$){
    win.ccoacharts = win.ccoacharts || {};
    
    /**
     * 创建表 一行多条数据
     * @param {Dom} dom
     * @param {Array} datas
        data = [
            nameA => [
                typeA => 1222,
                typeB => 123    
            ],
            nameB => [
                typeA => 323,
                typeB =>11    
            ],...
        ]
        
     * @param {Object[Array]} legend 分类说明
        legend = [typeA,typeB,..]
     * @returns
     */
    var MultiBarChart = function(config, dom, datas, legend){
        this.config = $.extend({
            title: '标题',                      //大标题
            subTtile: '',                       //副标题
        },config);
        this.init(dom,datas,legend);
        this.reflashChart(datas,legend);
        var _this = this;
        $(win).resize(function(){
            _this.chart.resize();
        });
    }
    var p = MultiBarChart.prototype;
    /** 制图画板 */
    p.canvas = null;
    /** 图表 */
    p.chart = null;
    /** 图表选项 */
    p.chartOptions = null;
    
    p.init = function(dom,datas,legend){
        var _this = this;
        this.canvas = dom;
        
        //重新计算图标的高度，高度由显示的数据相关
        var len=0;
        for(var i in datas)len++;
        $(this.canvas).css('height',(len*(30+10)-10+100)+"px");
        
        this.chart = echarts.init(dom);
        this.chart.on('legendselectchanged', function(params){_this.legendselectchanged(params,datas,legend)});
        this.chartOptions = {
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            legend: {
                data: ['板书'],
                selected:{}
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis:  {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: []
            },
            series: [
            ]
        };
    };
    
    /**
     * 刷新图标
     * @param {Array} datas
     * @param {Object[Array]} legend 分类说明
     * @returns 
     */
    p.reflashChart = function(datas,legend){
       //创建类型初始数据
        var _legend = [];
        var series = [];
        
        for(var id in legend)
        {
            _legend.push(legend[id]);
            series.push({
                    name: legend[id],
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: []
                })
        }
        //分类数据
        this.chartOptions.legend.data = _legend;
        this.chartOptions.series = series;
        this.chart.setOption(this.chartOptions,true);
        this.chart.dispatchAction({type: 'legendToggleSelect'})//初始显示所有数据
    }
    /**
     * 类型 显示/隐藏 状态改变事件
     * @param {Object} params   分类 显示/禁用 状态
     * @param {Array} datas 所有数据
     * @param {Object[Array]} legend 分类说明
     * @returns {void}
     */
    p.legendselectchanged = function(params,datas,legend){        
         //根据类型过滤算出汇总,得到从高到低的排序结果
        var all = this.getAllByLegend(datas,params.selected);

        var serie,index=0;
        for(var id in legend){
            //拿到对应分类 serie
            serie = this.chartOptions.series[index++];
            serie.data = [];
            //合并分类的 data 数据，由于series第一个会排序最底下再往上增，所以使用倒序合并
            for(var i=all.length-1;i>=0;i--)
                serie.data.push(datas[all[i].name][legend[id]]);
        }
        
        //生成每个bar的名称
        var yAxisData = [];
        for(var i=all.length-1;i>=0;i--)
            yAxisData.push(all[i]["name"]+"\n("+all[i]["value"]+")");
        
        
        this.chartOptions.yAxis.data = yAxisData;
        this.chartOptions.legend.selected = params.selected;
        this.chart.setOption(this.chartOptions,true);
    }
    /**
     * 类型过滤算出汇总
     * @param {Array} datas
     * @param {Array} filterLegend    显示的类型
     * @returns {Object}
     */
    p.getAllByLegend = function(datas,filterLegend){
        var arr = [];
        var vv = 0;
        for(var i in datas){
            vv = 0;
            for(var legend_name in datas[i])
            {
                //只计算显示的分类
                if(filterLegend[legend_name])
                    vv += Number(datas[i][legend_name]);
            }
            vv = Math.round(vv * 100) / 100;
            //添加到数组方便排序
            arr.push({name:i,value:vv});
        }
        return arr.sort(function(a,b){return b.value-a.value});
    }
    
    win.ccoacharts.MultiBarChart = MultiBarChart;
})(window,jQuery);




(function(win,$){
    
    win.ccoacharts = win.ccoacharts || {};
    /**
     * 创建表
     * @param {Object} config   配置
     * @param {Dom} dom         chart的容器
     * @param {Array} datas     [[name:string,value:number],[],...]
     * @returns
     */
    var BarChart = function(config,dom,datas){
        this.config = $.extend({
            title: '标题',                      //大标题
            subTtile: '',                       //副标题
            itemLabelFormatter: '{c}',           //bar 提示格式
        },config);
        this.init(dom,datas);
        this.reflashChart(this.config.title,datas);
        var _this = this;
        $(win).resize(function(){
            _this.chart.resize();
        });
    }
    var p = BarChart.prototype;
    /** 制图画板 */
    p.canvas = null;
    /** 图表 */
    p.chart = null;
    /** 图表选项 */
    p.chartOptions = null;
    
    p.init = function(dom,datas){
        this.canvas = dom;
        //重新计算图标的高度，高度由显示的数据相关
        var len=0;
        for(var i in datas)len++;
        $(this.canvas).css('height',(len*(30+10)-10+100)+"px");
        
        this.chart = echarts.init(dom);
        this.chartOptions = {
            title: {
                text: '世界人口总量',
                subtext: '数据来自课程中心平台'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: []
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value',
                boundaryGap: [0, 0.01]
            },
            yAxis: {
                type: 'category',
                data: ['巴西','印尼','美国','印度','中国','世界人口(万)']
            },
            series: [
                {
                    name: '',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position:"insideRight",
                            formatter:this.config.itemLabelFormatter
                        }
                    },
                    data: [18203, 23489, 29034, 104970, 131744, 630230]
                }
            ]
        };

    };
    
    /**
     * 刷新图标
     * @param {String} title 标题
     * @param {Array} data 出错步骤数据
     * @returns 
     */
    p.reflashChart = function(title,data){
        
        var keys = [];
        var values = [];
        
        for(var i=0,len=data.length;i<len;i++)
        {
            keys.push(data[i]["name"]);
            values.push(data[i]["value"]);
        }
        
        
        this.chartOptions.title.text = title;
        this.chartOptions.yAxis.data = keys;
        this.chartOptions.series[0].data = values;
        this.chart.setOption(this.chartOptions,true);
    }

    win.ccoacharts.BarChart = BarChart;
})(window,jQuery);

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function(win,$){
    
    win.ccoacharts = win.ccoacharts || {};
    /**
     * 创建表
     * @param {Object} config
     * @param {Dom} dom
     * @param {Array} datas
     * @returns
     */
    var PicChart = function(config,dom,datas,legend){
        this.config = $.extend({
            title: '标题',                      //大标题
            subTtile: '',                       //副标题
            itemLabelFormatter: '{b} ( {c} ) {d}%',             //bar 提示格式
            tooltipFormatter:'{a} <br/>{b} : {c} ({d}%)',       //鼠标移上去提示格式
        },config);
        
        this.init(dom);
        this.reflashChart(datas,legend);
        var _this = this;
        $(win).resize(function(){
            _this.chart.resize();
        });
    }
    var p = PicChart.prototype;
    /** 制图画板 */
    p.canvas = null;
    /** 图表 */
    p.chart = null;
    /** 图表选项 */
    p.chartOptions = null;
    
    p.init = function(dom){
        this.canvas = dom;
        this.chart = echarts.init(dom);
        this.chartOptions = {
            title : {
                text: this.config.title,
                subtext: this.config.subTtile,
                x:'left'
            },
            tooltip : {
                trigger: 'item',
                formatter: this.config.tooltipFormatter,
                position: ($(dom).width() >= 720 ? null : ['0%', '15%'])
            },
            legend: {
                data: [],
                show: false
            },
            series : [
                {
                    name: '',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '60%'],
                    data:[
                        /*
                        {value:335, name:'直接访问'},
                        {value:310, name:'邮件营销'},
                        {value:234, name:'联盟广告'},
                        {value:135, name:'视频广告'},
                        {value:1548, name:'搜索引擎'} 
                         */
                    ],
                    label:{
                        normal:{
                            show:true,
                            formatter:this.config.itemLabelFormatter,
                            left:'left'
                        }
                    },
                    
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
    };
    
    /**
     * 刷新图标
     * @param {String} title 标题
     * @param {Array} data 出错步骤数据
     * @param {Array} legend 类型
     * @returns 
     */
    p.reflashChart = function(data,legend){
        this.chartOptions.legend.data = legend;
        this.chartOptions.series[0].data = data;
        this.chart.setOption(this.chartOptions);
    }
    win.ccoacharts.PicChart = PicChart;
})(window,jQuery);