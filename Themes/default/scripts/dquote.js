/**
 * Project: dQuote Selection
 * Version: 2.6
 * File: dquote.js
 * Author: digger @ http://mysmf.ru
 * License: CC BY-NC-ND 4.0 http://creativecommons.org/licenses/by-nc-nd/4.0/
 */

var dQuoteText;

if (typeof oQuickReply != 'undefined') {
    oQuickReply.quote = insertReplyText;
}

function getSelectedText() {

    if (window.getSelection) {
        return window.getSelection().toString();
    }
    else if (document.getSelection) {
        return document.getSelection();
    }
    else if (document.selection) {
        return document.selection.createRange().text;
    }
    return false;
}

function onTextReceived(XMLDoc) {

    var sQuoteText = '';
    for (var i = 0; i < XMLDoc.getElementsByTagName('quote')[0].childNodes.length; i++)
        sQuoteText += XMLDoc.getElementsByTagName('quote')[0].childNodes[i].nodeValue;

    if (typeof oEditorHandle_message != 'undefined' && oEditorHandle_message.bRichTextEnabled) {
        if (dQuoteText) oEditorHandle_message.insertText(sQuoteText.match(/^\[quote(.*)]/ig) + dQuoteText + '[/quote]' + '<br />', false, true)
        else oEditorHandle_message.insertText(sQuoteText + '<br />', false, true);
    }
    else {
        if (dQuoteText) document.forms.postmodify.message.value += sQuoteText.match(/^\[quote(.*)]/ig) + dQuoteText + '[/quote]' + '\n'
        else document.forms.postmodify.message.value += sQuoteText + '\n';
    }

    dQuoteText = '';
    ajax_indicator(false);
}

function insertReplyText(msgId) {

    dQuoteText = getSelectedText();
    ajax_indicator(true);
    getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=quotefast;quote=' + msgId + ';xml;pb=message', onTextReceived);
    if (oQuickReply.bCollapsed) oQuickReply.swap();

    window.location.hash = '#quickreply';

    return false;
}


