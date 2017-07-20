/**
 * Form WYSIWYG demo
 */
(function ($) {
  'use strict';

  // Summernote
  $('.summernote').summernote();

  // Bootstrap
  $('.bootstrap-wysiwyg').wysihtml5({
    toolbar: {
      fa: true
    }
  });
})(jQuery);