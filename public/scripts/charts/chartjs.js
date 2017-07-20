/**
 * ChartJS chart page
 */
(function ($) {
  'use strict';

  var helpers = Chart.helpers;

  // Define global settings
  Chart.defaults.global.responsive = true;
  Chart.defaults.global.scaleFontFamily = $.staticApp.font;
  Chart.defaults.global.scaleFontSize = 10;
  Chart.defaults.global.tooltipFillColor = $.staticApp.primary;
  Chart.defaults.global.tooltipFontFamily = $.staticApp.font;
  Chart.defaults.global.tooltipFontSize = 12;
  Chart.defaults.global.tooltipTitleFontFamily = $.staticApp.font;
  Chart.defaults.global.tooltipTitleFontSize = 13;
  Chart.defaults.global.tooltipTitleFontStyle = '700';
  Chart.defaults.global.tooltipCornerRadius = 0;

  // Polar chart
  var polarChartData = [{
    value: Math.random(),
    color: $.staticApp.danger
  }, {
    value: Math.random(),
    color: $.staticApp.info
  }, {
    value: Math.random(),
    color: $.staticApp.warning
  }, {
    value: Math.random(),
    color: $.staticApp.bodyBg
  }, {
    value: Math.random(),
    color: $.staticApp.dark
  }, {
    value: Math.random(),
    color: $.staticApp.primary
  }];
  var polar = $('.polar').get(0).getContext('2d');
  var myPolarArea = new Chart(polar).PolarArea(polarChartData, {
    segmentShowStroke: false,
    scaleBackdropColor: 'rgba(255,255,255,1)',
    scaleShowLine: false,
  });

  //Radar chart
  var radarChartData = {
    labels: ['Eating', 'Drinking', 'Sleeping', 'Designing', 'Coding', 'Partying', 'Running'],
    datasets: [{
      fillColor: 'rgba(220,220,220,1)',
      strokeColor: 'rgba(220,220,220,1)',
      pointColor: 'rgba(220,220,220,1)',
      pointStrokeColor: '#fff',
      data: [65, 59, 90, 81, 56, 55, 40]
    }, {
      fillColor: 'rgba(151,187,205,1)',
      strokeColor: 'rgba(151,187,205,1)',
      pointColor: 'rgba(151,187,205,1)',
      pointStrokeColor: '#fff',
      data: [28, 48, 40, 19, 96, 27, 100]
    }]
  };
  var radar = $('.radar').get(0).getContext('2d');
  var myRadar = new Chart(radar).Radar(radarChartData, {
    pointDotRadius: 0,
    pointLabelFontFamily: '\'Roboto\'',
    pointLabelFontSize: 10
  });

  // Doughnut chart
  var doughnutData = [{
    value: 280,
    color: $.staticApp.danger,
    highlight: LightenDarkenColor($.staticApp.danger),
    label: 'Danger'
  }, {
    value: 70,
    color: $.staticApp.success,
    highlight: LightenDarkenColor($.staticApp.success),
    label: 'Success'
  }, {
    value: 100,
    color: $.staticApp.warning,
    highlight: LightenDarkenColor($.staticApp.warning),
    label: 'Warning'
  }, {
    value: 40,
    color: $.staticApp.bodyBg,
    highlight: LightenDarkenColor($.staticApp.bodyBg),
    label: 'Body'
  }, {
    value: 120,
    color: $.staticApp.dark,
    highlight: LightenDarkenColor($.staticApp.dark),
    label: 'Dark'
  }];
  var donut = $('.doughnut').get(0).getContext('2d');
  var myDoughnut = new Chart(donut).Doughnut(doughnutData, {
    percentageInnerCutout: 60,
  });
  var legendHolder = document.createElement('div');
  legendHolder.innerHTML = myDoughnut.generateLegend();
  // Include a html legend template after the module doughnut itself
  helpers.each(legendHolder.firstChild.childNodes, function (legendNode, index) {
    helpers.addEvent(legendNode, 'mouseover', function () {
      var activeSegment = myDoughnut.segments[index];
      activeSegment.save();
      activeSegment.fillColor = activeSegment.highlightColor;
      myDoughnut.showTooltip([activeSegment]);
      activeSegment.restore();
    });
  });
  helpers.addEvent(legendHolder.firstChild, 'mouseout', function () {
    myDoughnut.draw();
  });
  $('.doughnut').parent().append(legendHolder.firstChild);

  // Bar graph
  var getRandomArbitrary = function () {
    return Math.round(Math.random() * 100);
  };
  var barChartData = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    datasets: [{
      fillColor: 'rgba(220,220,220,1)',
      strokeColor: 'rgba(220,220,220,1)',
      highlightFill: 'rgba(220,220,220,1)',
      highlightStroke: 'rgba(220,220,220,1)',
      data: [getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary()]
    }, {
      fillColor: 'rgba(151,187,205,1)',
      strokeColor: 'rgba(151,187,205,1)',
      highlightFill: 'rgba(151,187,205,1)',
      highlightStroke: 'rgba(151,187,205,1)',
      data: [getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary()]
    }]
  };
  var bar = $('.bar').get(0).getContext('2d');
  var myBar = new Chart(bar).Bar(barChartData, {
    scaleShowGridLines: false,
    barShowStroke: false
  });

  // Line chart
  var lineChartData = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    datasets: [{
      label: 'My First dataset',
      fillColor: 'rgba(220,220,220,0.2)',
      strokeColor: 'rgba(220,220,220,1)',
      pointColor: 'rgba(220,220,220,1)',
      pointStrokeColor: '#fff',
      pointHighlightFill: '#fff',
      pointHighlightStroke: 'rgba(220,220,220,1)',
      data: [getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary()]
    }, {
      label: 'My Second dataset',
      fillColor: 'rgba(151,187,205,0.2)',
      strokeColor: 'rgba(151,187,205,1)',
      pointColor: 'rgba(151,187,205,1)',
      pointStrokeColor: '#fff',
      pointHighlightFill: '#fff',
      pointHighlightStroke: 'rgba(151,187,205,1)',
      data: [getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary(), getRandomArbitrary()]
    }]
  };
  var line = $('.line').get(0).getContext('2d');
  var myLine = new Chart(line).Line(lineChartData, {
    scaleShowGridLines: false,
    bezierCurve: false,
    pointDotRadius: 2,
    datasetStrokeWidth: 1,
  });

  // Pie chart
  var pieData = [{
    value: 300,
    color: $.staticApp.danger,
    highlight: LightenDarkenColor($.staticApp.danger, 20),
    label: 'Danger'
  }, {
    value: 50,
    color: $.staticApp.success,
    highlight: LightenDarkenColor($.staticApp.success, 20),
    label: 'Success'
  }, {
    value: 100,
    color: $.staticApp.warning,
    highlight: LightenDarkenColor($.staticApp.warning, 20),
    label: 'Warning'
  }, {
    value: 40,
    color: $.staticApp.bodyBg,
    highlight: LightenDarkenColor($.staticApp.bodyBg, 20),
    label: 'Body'
  }, {
    value: 120,
    color: $.staticApp.dark,
    highlight: LightenDarkenColor($.staticApp.dark, 20),
    label: 'Dark'
  }];
  var pie = $('.pie').get(0).getContext('2d');
  var myPie = new Chart(pie).Pie(pieData, {
    segmentShowStroke: false
  });
})(jQuery);