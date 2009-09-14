/**
 * AJAX Nette Framework plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
    nette: {
        updateSnippet: function (id, html) {
            $("#" + id).html(html);
        },

        error: function(XMLHttpRequest, textStatus, errorThrown){
            var MessageTxt = "<div style=\"font-size: 120%;font-weight: bold;\">Informace o přenosu</div>";
            MessageTxt += "<i>TextStatus</i>: <b>"+textStatus+"</b><br>";
            MessageTxt += "<i>ErrorThrown</i>: <b>"+errorThrown+"</b><br>";
            MessageTxt += "<i>Status</i>: <b>"+XMLHttpRequest.statusText+" ("+XMLHttpRequest.status+")</b>";
            if(XMLHttpRequest.getResponseHeader("Content-type")=="application/json")
            {
                var errorInfo = eval("["+XMLHttpRequest.responseText+"]");
                errorInfo = errorInfo[0];
                MessageTxt += "<div style=\"border-top: 1px solid white;margin: 5px;margin-right: 0px; margin-left: 0px;\"></div>"
                MessageTxt += "<div style=\"font-size: 120%;font-weight: bold;\">Informace o chybě ze serveru</div>";
                for(var i in errorInfo){
                    MessageTxt += "<i>"+i+"</i>: <b>"+errorInfo[i]+"</b><br>";
                }
            }
            MessageTxt += "<div style=\"border-top: 1px solid white;margin: 5px;margin-right: 0px; margin-left: 0px;\"></div>"
            MessageTxt += "<i>Byla vygenerována chybová zpráva, která je p\u0159ístupná administrátorovi. Prosím požádejte administrátora o opravu chyby. Omlouváme se za způsobené potíže.</i><br>";
            message(MessageTxt, "Chyba (neo\u0161etřená výjimka)", "error",true);
        },

        success: function (payload) {
            // redirect
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }

            // snippets
            if (payload.snippets) {
                for (var i in payload.snippets) {
                    jQuery.nette.updateSnippet(i, payload.snippets[i]);
                }
            }
        }
    }
});

jQuery.ajaxSetup({
    success: jQuery.nette.success,
    dataType: "json",
    error: jQuery.nette.error
});

$("a[href]").livequery("click",function(e){
    e.preventDefault();
    $.get($(this).attr("href"));
})