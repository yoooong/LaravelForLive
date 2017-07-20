/**
 * Calendar app page
 */
(function ($) {
  'use strict';

  var colorClass;

  function externalEvents(elm) {
    var eventObject = {
      title: $.trim(elm.text()),
      className: elm.data('class')
    };
    elm.data('eventObject', eventObject);
    elm.draggable({
      zIndex: 999,
      revert: true,
      revertDuration: 0
    });
  }

  $('.add-event').click(function (e) {
    var markup = $('<div class=\'external-event event-primary\' data-class=\'event-primary\'>New event</div>');
    $('.external-events').append(markup);
    externalEvents(markup);
    e.preventDefault();
    e.stopPropagation();
  });

  $('#external-events div.external-event').each(function () {
    externalEvents($(this));
  });

  $('.fullcalendar').fullCalendar({
    height: $(window).height() - $('.header').height() - $('.content-footer').height() - 25,
    editable: true,
    defaultView: 'month',
    header: {
      left: 'today prev,next',
      right: 'title month,agendaWeek,agendaDay'
    },
    buttonIcons: {
      prev: ' fa fa-caret-left',
      next: ' fa fa-caret-right',
    },
    droppable: true,
    axisFormat: 'h:mm',
    columnFormat: {
      month: 'dddd',
      week: 'ddd M/D',
      day: 'dddd M/d',
      agendaDay: 'dddd D'
    },
    allDaySlot: false,
    drop: function (date) {
      var originalEventObject = $(this).data('eventObject');
      var copiedEventObject = $.extend({}, originalEventObject);
      copiedEventObject.start = date;
      $('.fullcalendar').fullCalendar('renderEvent', copiedEventObject, true);
      if ($('#drop-remove').is(':checked')) {
        $(this).remove();
      }
    },
    defaultDate: moment().format('YYYY-MM-DD'),
    viewRender: function (view, element) {
      if (!$('.fc-toolbar .fc-left .fc-t-events').length) {
        $('.fc-toolbar .fc-left').prepend($('<button type="button" class="fc-button fc-state-default fc-corner-left fc-corner-right fc-t-events"><i class="icon-list"></i></button>').on('click', function () {
          $('.events-sidebar').toggleClass('hide');
        }));
      }
    },
    events: [{
      title: 'Go Shopping',
      start: moment().format('YYYY-MM-DD'),
      className: 'event-success'
    }, {
      title: 'Launch Product',
      start: moment(moment().format('YYYY-MM-DD')).subtract(1, 'day').format('YYYY-MM-DD'),
      className: 'event-primary'
    }, {
      title: 'Meeting',
      start: moment(moment().format('YYYY-MM-DD')).subtract(6, 'day').format('YYYY-MM-DD'),
      end: moment(moment().format('YYYY-MM-DD')).subtract(3, 'day').format('YYYY-MM-DD'),
      className: 'event-info'
    }, {
      title: 'Lunch',
      start: moment(moment().format('YYYY-MM-DD')).add(4, 'day').format('YYYY-MM-DD'),
      className: 'event-warning'
    }, {
      title: 'Go to link',
      url: 'http://nyasha.me/',
      start: moment(moment().format('YYYY-MM-DD')).add(8, 'day').format('YYYY-MM-DD'),
      className: 'event-danger'
    }]
  });
})(jQuery);