/**
 * $Id$
 *
 *
 */
urls.message = {
    // TODO: map severity to CSS class
    show: function(msg, sev) {
        $("#message").html(msg);

        switch(sev) {
            case "info":
                cssSeverity = "info-msg";
                break;
            case "warn":
                cssSeverity = "warn-msg";
                break;
            case "error":
                cssSeverity = "error-msg";
                break;
            case "debug":
                cssSeverity = "debug-msg";
                break;
            default:
                cssSeverity = "info-msg";
        }
        $("#message_bar").removeClass("info-msg warn-msg error-msg debug-msg");
        $("#message_bar").addClass(cssSeverity);

        $("#message_container").attr("style", "visibility: visible").delay(7000).fadeTo(1000, 0);
        $("#shorten").focus();
    },
    hide: function() {
        $("#message_container").attr("style", "visibility: hidden");
        $("#message").html('');
        $("#message_bar").removeClass("info-msg warn-msg error-msg debug-msg");
        return false; // prevent default action
    }
};