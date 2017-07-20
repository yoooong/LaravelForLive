/**
 * Products commerce page
 */
(function ($) {
  'use strict';

  var elem = document.querySelector('.tile-container');

  var iso = new Isotope(elem, {
    itemSelector: '.tile',
    masonry: {
      columnWidth: '.tile-sizer'
    },
    transitionDuration: '0.15s'
  });

  $('.tile-container').imagesLoaded().always(function () {}).progress(function (instance, image) {
    if (image.isLoaded) {
      iso.layout();
    }
  }).done(function () {
    iso.layout();
  });

  $(window).smartresize(function () {
    iso.layout();
  });

  $('[data-toggle=layout-small-menu]').on('click', function () {
    iso.layout();
  });

})(jQuery);