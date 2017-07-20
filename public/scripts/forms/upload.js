/**
 * Form fileupload demo
 */
(function ($) {
  'use strict';

  // Initialize the jQuery File Upload widget:
  $('#fileupload').fileupload({
    // Uncomment the following to send cross-domain cookies:
    //xhrFields: {withCredentials: true},
    url: 'media/post/'
  });

  // Enable iframe cross-domain access via redirect option:
  $('#fileupload').fileupload('option', 'redirect', window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s'));
  if (window.location.hostname === 'blueimp.github.io') {
    // Demo settings:
    $('#fileupload').fileupload('option', {
      url: '//jquery-file-upload.appspot.com/',
      disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
      maxFileSize: 999000,
      acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
    });

    if ($.support.cors) {
      $.ajax({
        url: '//jquery-file-upload.appspot.com/',
        type: 'HEAD'
      }).fail(function () {
        $('<div class="alert alert-danger"/>').text('Upload server currently unavailable - ' + new Date()).appendTo('#fileupload');
      });
    }
  } else {
    $('#fileupload').addClass('fileupload-processing');
    $.ajax({
      // Uncomment the following to send cross-domain cookies:
      //xhrFields: {withCredentials: true},
      url: $('#fileupload').fileupload('option', 'url'),
      dataType: 'json',
      context: $('#fileupload')[0]
    }).always(function () {
      $(this).removeClass('fileupload-processing');
    }).done(function (result) {
      $(this).fileupload('option', 'done').call(this, $.Event('done'), {
        result: result
      });
    });
  }
})(jQuery);