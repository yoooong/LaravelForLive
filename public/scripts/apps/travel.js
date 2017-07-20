/**
 * Travel app page
 */
(function ($) {
  'use strict';

  var latitude = 35.784,
    longitude = -78.670,
    map_zoom = 6,
    is_internetExplorer11 = navigator.userAgent.toLowerCase().indexOf('trident') > -1,
    marker_url = (is_internetExplorer11) ? 'images/cd-icon-location.png' : 'images/cd-icon-location.svg';

  var map_options = {
    scrollwheel: false,
    center: new google.maps.LatLng(latitude, longitude),
    zoom: map_zoom,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  var map = new google.maps.Map(document.getElementById('google-container'), map_options);

  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(latitude, longitude),
    map: map,
    visible: true,
    icon: marker_url,
  });

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
})(jQuery);