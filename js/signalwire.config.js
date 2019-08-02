(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.signalwireConfigurations = {
        attach: function (context, settings) {
            var senderType = drupalSettings.signalwire.setting.sender_type;

            $('.signalwire_sender_type #edit-sender-type').once().click(function () {
                var senderType = $("input[name='sender_type']:checked").val();
                displaySender(senderType);
            });
        }
    };
    function displaySender(senderType){
        var senderDiv = $('#signalwire_from');

        if (senderType === 'none') {
            senderDiv.removeClass("unhide_from_number");
            senderDiv.addClass("hide_from_number");
        }else{
            senderDiv.removeClass("hide_from_number");
            senderDiv.addClass("unhide_from_number");
        }
    }
}(jQuery, Drupal, drupalSettings));