(function ($, Drupal, drupalSettings) {
    $(document).ready(function(){
        $('#leave_from').on('change', function(e){
            let leave_from = $('#leave_from').val;
            console.log(leave_from);
        });
        $('#leave_to').on('change', function(e){
            let leave_to = $('#leave_to').val;
            console.log(leave_to);
        });
        $('.leave_reason').on('blur', function(e){
            let leave_reason = $('.leave_to').val;
            console.log(leave_reason);
        });
    })
}) (jQuery, Drupal, drupalSettings);