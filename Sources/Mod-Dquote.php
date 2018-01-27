<?php
/**
 * Project: dQuote Selection
 * Version: 2.6.3
 * File: Mod-dQuote.php
 * Author: digger @ http://mysmf.ru
 * License: The MIT License (MIT)
 */

if (!defined('SMF'))
    die('Hacking attempt...');

/**
 * Load all needed hooks
 */
function loadDquoteHooks()
{
    add_integration_function('integrate_menu_buttons', 'addDquoteCopyright', false);
    add_integration_function('integrate_menu_buttons', 'loadDquoteJS', false);
}


/**
 * Add mod copyright to the forum credit's page
 */
function addDquoteCopyright()
{
    global $context;

    if ($context['current_action'] == 'credits')
        $context['copyrights']['mods'][] = '<a href="http://mysmf.net/mods/dquote-selection" target="_blank">dQuoteSelection</a> &copy; 2007-2018, digger';
}


/**
 * Load all needed js & css
 */
function loadDquoteJS()
{
    global $context, $settings, $options, $txt;

    if (empty($context['current_topic']) && empty($context['can_quote'])) return;

    // Load language file
    loadLanguage('Dquote/Dquote');

    // Full Reply
    if ($context['current_action'] == 'post' || $context['current_action'] == 'post2') {
        $txt['bbc_quote'] = $txt['dQuoteSelection_txt'];
    } // Quick Reply
    else if (!empty($options['display_quick_reply'])) {
        $txt['quote'] = $txt['dQuoteSelection_txt'];
    }

    // Load JS
    $context['insert_after_template'] .= '
        <script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dquote.js?263"></script>';

}
