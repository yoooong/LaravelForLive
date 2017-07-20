/**
 * Nvd3 chart page
 */
(function ($) {
  'use strict';

  // Line chart
  function sinAndCos() {
    var sin = [],
      sin2 = [],
      cos = [];
    for (var i = 0; i < 100; i++) {
      sin.push({
        x: i,
        y: Math.sin(i / 10)
      });
      sin2.push({
        x: i,
        y: Math.sin(i / 10) * 0.25 + 0.5
      });
      cos.push({
        x: i,
        y: 0.5 * Math.cos(i / 10)
      });
    }
    return [{
      values: sin,
      key: 'Sine Wave',
      color: $.staticApp.primary
    }, {
      values: cos,
      key: 'Cosine Wave',
      color: $.staticApp.danger
    }, {
      values: sin2,
      key: 'Another sine wave',
      color: $.staticApp.info
    }];
  }
  nv.addGraph(function () {
    var lineChart = nv.models.lineChart();
    var height = 300;
    lineChart.useInteractiveGuideline(true);
    lineChart.xAxis.tickFormat(d3.format(',r'));
    lineChart.yAxis.axisLabel('Voltage (v)').tickFormat(d3.format(',.2f'));
    d3.select('.line svg').attr('perserveAspectRatio', 'xMinYMid').datum(sinAndCos()).transition().duration(500).call(lineChart);
    nv.utils.windowResize(lineChart.update);
    return lineChart;
  });

  // Historical bar chart
  var historicalBarChart = [{
    key: 'Cumulative Return',
    values: [{
      'label': 'A Label',
      'value': -29.765957771107
    }, {
      'label': 'B Label',
      'value': 0
    }, {
      'label': 'C Label',
      'value': 32.807804682612
    }, {
      'label': 'D Label',
      'value': 196.45946739256
    }, {
      'label': 'E Label',
      'value': 0.19434030906893
    }, {
      'label': 'F Label',
      'value': -98.079782601442
    }, {
      'label': 'G Label',
      'value': -13.925743130903
    }, {
      'label': 'H Label',
      'value': -5.1387322875705
    }]
  }];
  nv.addGraph(function () {
    var barChart = nv.models.discreteBarChart().x(function (d) {
        return d.label;
      })
      .y(function (d) {
        return d.value;
      }).staggerLabels(true)
      .showValues(true)
      .duration(350);
    d3.select('.bar svg').datum(historicalBarChart).call(barChart);
    nv.utils.windowResize(barChart.update);
    return barChart;
  });

  // Scatter chart
  var i, j, scatterChart;
  nv.addGraph(function () {
    scatterChart = nv.models.scatterChart().showDistX(true)
      .showDistY(true).duration(350).color(d3.scale.category10().range());
    scatterChart.xAxis.tickFormat(d3.format('.02f'));
    scatterChart.yAxis.tickFormat(d3.format('.02f'));
    var myData = randomData(4, 40);
    d3.select('.scatter svg').datum(myData).call(scatterChart);
    nv.utils.windowResize(scatterChart.update);
    return scatterChart;
  });

  function randomData(groups, points) {
    var data = [],
      shapes = ['circle', 'cross', 'triangle-up', 'triangle-down', 'diamond', 'square'],
      random = d3.random.normal();
    for (i = 0; i < groups; i++) {
      data.push({
        key: 'Group ' + i,
        values: []
      });
      for (j = 0; j < points; j++) {
        data[i].values.push({
          x: random(),
          y: random(),
          size: Math.random(),
          shape: (Math.random() > 0.95) ? shapes[j % 6] : 'circle'
        });
      }
    }
    return data;
  }

  // Multibar chart
  var long_short_data = [{
    'key': 'Series 1',
    'color': $.staticApp.primary,
    'values': [{
      'label': 'Group A',
      'value': -1.8746444827653
    }, {
      'label': 'Group B',
      'value': -8.0961543492239
    }, {
      'label': 'Group C',
      'value': -0.57072943117674
    }, {
      'label': 'Group D',
      'value': -2.4174010336624
    }, {
      'label': 'Group E',
      'value': -0.72009071426284
    }, {
      'label': 'Group F',
      'value': -0.77154485523777
    }, {
      'label': 'Group G',
      'value': -0.90152097798131
    }, {
      'label': 'Group H',
      'value': -0.91445417330854
    }, {
      'label': 'Group I',
      'value': -0.055746319141851
    }]
  }, {
    'key': 'Series 2',
    'color': $.staticApp.info,
    'values': [{
      'label': 'Group A',
      'value': 25.307646510375
    }, {
      'label': 'Group B',
      'value': 16.756779544553
    }, {
      'label': 'Group C',
      'value': 18.451534877007
    }, {
      'label': 'Group D',
      'value': 8.6142352811805
    }, {
      'label': 'Group E',
      'value': 7.8082472075876
    }, {
      'label': 'Group F',
      'value': 5.259101026956
    }, {
      'label': 'Group G',
      'value': 0.30947953487127
    }, {
      'label': 'Group H',
      'value': 0
    }, {
      'label': 'Group I',
      'value': 0
    }]
  }];
  var multiChart;
  nv.addGraph(function () {
    multiChart = nv.models.multiBarHorizontalChart().x(function (d) {
        return d.label;
      }).y(function (d) {
        return d.value;
      }).margin({
        top: 30,
        right: 20,
        bottom: 50,
        left: 175
      }).showValues(true)
      .duration(350).showControls(true);
    multiChart.yAxis.tickFormat(d3.format(',.2f'));
    d3.select('.multi svg').datum(long_short_data).call(multiChart);
    nv.utils.windowResize(multiChart.update);
    return multiChart;
  });
})(jQuery);