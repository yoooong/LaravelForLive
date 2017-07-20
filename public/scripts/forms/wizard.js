/**
 * Form wizard demo
 */
(function ($) {
  'use strict';

  // Credit card form
  $('#wizardForm').card({
    container: '.credit-card'
  });

  // Checkbo plugin
  $('.checkbo').checkBo();

  // Jquery validator
  var $validator = $('#wizardForm').validate({
    rules: {
      emailfield: {
        required: true,
        email: true,
        minlength: 3
      },
      namefield: {
        required: true,
        minlength: 3
      },
      passwordfield: {
        required: true,
        minlength: 6
      },
      cpasswordfield: {
        required: true,
        minlength: 6,
        equalTo: '#passwordfield'
      },
      description: {
        required: true
      },
      number: {
        required: true
      },
      name: {
        required: true
      },
      expiry: {
        required: true
      },
      cvc: {
        required: true
      }
    }
  });

  function checkValidation() {
    var $valid = $('#wizardForm').valid();
    if (!$valid) {
      $validator.focusInvalid();
      return false;
    }
  }

  // Twitter bootstrap wizard
  $('#rootwizard').bootstrapWizard({
    tabClass: '',
    'nextSelector': '.button-next',
    'previousSelector': '.button-previous',
    onNext: checkValidation,
    onLast: checkValidation,
    onTabClick: checkValidation
  });
})(jQuery);