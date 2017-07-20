/**
 * Form plugins demo
 */
(function ($) {
  'use strict';

  // Color picker
  $('.color-picker').colorpicker();
  $('.color-picker2').colorpicker({
    horizontal: true
  });

  // Timepicker
  $('.time-picker').timepicker();

  // Clockpicker
  $('.clockpicker').clockpicker({
    donetext: 'Done'
  });

  // Input tags
  $('#tags').tagsInput({
    width: 'auto'
  });

  // Chosen plugin
  $('.chosen').chosen({
    width: '100%'
  });
  $('.chosen-select').chosen({
    disable_search_threshold: 10,
    width: '100%'
  });

  // Checkbo plugin
  $('.checkbo').checkBo();

  // Telephone input plugin
  $('.telephone-input').intlTelInput();

  // Touchspin plugin
  $('.spinner1').TouchSpin({
    initval: 0,
    buttondown_class: 'btn btn-primary',
    buttonup_class: 'btn btn-primary'
  });
  $('.spinner2').TouchSpin({
    initval: 0,
    buttondown_class: 'btn btn-default',
    buttonup_class: 'btn btn-default'
  });

  // Daterange picker
  $('.drp').daterangepicker({
    format: 'YYYY-MM-DD',
    startDate: '2015-01-01',
    endDate: '2015-12-31'
  });

  // Multiselect plugin
  $('#pre-selected-options').multiSelect();
  $('#optgroup').multiSelect({
    selectableOptgroup: true
  });

  // Maxlength plugin
  $('#maxlength').maxlength({
    threshold: 20
  });
  $('#maxlengthConf').maxlength({
    alwaysShow: true,
    threshold: 10,
    warningClass: 'label label-info',
    limitReachedClass: 'label label-warning',
    placement: 'top',
    preText: 'used ',
    separator: ' of ',
    postText: ' chars.'
  });

  // Labelauty plugin
  $('.to-labelauty').labelauty({
    minimum_width: '155px',
    class: 'labelauty btn-block'
  });
  $('.to-labelauty-icon').labelauty({
    label: false
  });

  //Twitter typeahead plugin
  var statesList = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'];
  var states = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    // `states` is an array of state names defined in "The Basics"
    local: statesList
  });
  var bestPictures = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '../data/post_1960.json',
    remote: {
      url: '../data/films/queries/%QUERY.json',
      wildcard: '%QUERY'
    }
  });
  $('.typeahead-states').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
  }, {
    name: 'states',
    source: states
  });
  $('.typeahead-oscars').typeahead(null, {
    name: 'best-pictures',
    display: 'value',
    source: bestPictures
  });

  // Select2 plugin
  $('.select2').select2();

  // Selectize plugin
  $('#input-tags').selectize({
    delimiter: ',',
    persist: false,
    create: function (input) {
      return {
        value: input,
        text: input
      };
    }
  });
  $('#select-beast').selectize({
    create: true,
    sortField: 'text'
  });
})(jQuery);