/**
 * Noty notifications demo page
 */
(function ($) {
  'use strict';

  $('.chosen-select').chosen({
    disable_search_threshold: 10
  });

  var i = -1,
    msgs = ['Your request has succeded!', 'Are you the six fingered man?', 'Inconceivable!', 'I do not think that means what you think it means.', 'Have fun storming the castle!'];

  $('.show-messenger').on('click', function () {
    var msg = $('#message').val(),
      type = $('#messenger-type').val().toLowerCase(),
      position = $('#position').val();
    if (!msg) {
      msg = getMessage();
    }
    if (!type) {
      type = 'error';
    }
    noty({
      theme: 'app-noty',
      text: msg,
      type: type,
      timeout: 3000,
      layout: position,
      closeWith: ['button', 'click'],
      animation: {
        open: 'in',
        close: 'out'
      },
    });
  });

  function getMessage() {
    i++;
    if (i === msgs.length) {
      i = 0;
    }
    return msgs[i];
  }
})(jQuery);