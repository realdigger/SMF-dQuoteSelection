<?php
/**
 * Project: dQuote Selection
 * Version: 2.7.0
 * File: Mod-dQuote.php
 * Author: digger @ http://mysmf.ru
 * License: The MIT License (MIT)
 */

if (!defined('SMF')) {
    die('Hacking attempt...');
}

/**
 * Load all needed hooks
 */
function loadDquoteHooks()
{
    add_integration_function('integrate_menu_buttons', 'addDquoteCopyright', false);
    add_integration_function('integrate_menu_buttons', 'loadDquoteJS', false);
    add_integration_function('integrate_dquote_notification', 'sendDquoteNotification', false);
}

/**
 * Send notification to quote author if needed
 * @param $msgOptions
 * @param $topicOptions
 * @param $posterOptions
 * @return bool
 */
function sendDquoteNotification($msgOptions, $topicOptions, $posterOptions)
{
    global $txt, $sourcedir, $scripturl, $mbname;
    require_once($sourcedir . '/Subs-Auth.php');

    // Get all quote authors from post
    $quotesCount = preg_match_all('/\[quote\s+author=(.+)\s+link/U', $msgOptions['body'], $quotes);
    if (!$quotesCount) {
        return false;
    }
    $quotes = array_unique($quotes[1]);

    // Prepare email
    loadLanguage('Dquote/Dquote');
    $body = $posterOptions['name'] . ' ' . $txt['dQuoteSelection_notify_txt'] . ' ' . $scripturl . '?topic=' . $topicOptions['id'] . '.msg' . $msgOptions['id'] . '#msg' . $msgOptions['id'] . "\n\r\n\r" . $txt['regards_team'];
    $recipients = findMembers($quotes);
    $emails = array();

    // Check recipients
    foreach ($recipients as $recipient) {
        if ($recipient['id'] == $posterOptions['id']) continue;
        $emails[] = $recipient['email'];
    }

    sendmail($emails, $txt['dQuoteSelection_mail_subject_txt'], $body);

    return true;
}

/**
 * Add mod copyright to the forum credit's page
 */
function addDquoteCopyright()
{
    global $context;

    if ($context['current_action'] == 'credits') {
        $context['copyrights']['mods'][] = '<a href="https://mysmf.net/mods/dquote-selection" target="_blank">dQuoteSelection</a> &copy; 2007-2020, digger';
    }
}


/**
 * Load all needed js & css
 */
function loadDquoteJS()
{
    global $context, $settings, $options, $txt;

    if (empty($context['current_topic']) && empty($context['can_quote'])) {
        return;
    }

    loadLanguage('Dquote/Dquote');

    // Full Reply
    if ($context['current_action'] == 'post' || $context['current_action'] == 'post2') {
        $txt['bbc_quote'] = $txt['dQuoteSelection_txt'];
    } // Quick Reply
    elseif (!empty($options['display_quick_reply'])) {
        $txt['quote'] = $txt['dQuoteSelection_txt'];
    }

    // Load JS
    $context['insert_after_template'] .= '
        <script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dquote.js?270"></script>';
}
