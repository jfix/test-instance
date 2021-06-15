/*
 * library that manages user interface actions and display
 * $Id$
 */

var DEBUG = true;

var urls = {
    apipage: "/yourls-api.php"
};

$(document).ready(function(){
    $("#urls").focus().select();

    $("#message_dismiss").click(function(){
        urls.message.hide();
    });

    $("#batch_form").submit(function() {
        urls.message.show("URLs are being generated, this may take a while.", "info");
        $("#message_container").delay(2000).fadeTo(3000, 0);
    });

 });