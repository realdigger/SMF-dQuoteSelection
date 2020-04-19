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

class Dquote
{

    /**
     * Load all needed hooks
     */
    public static function loadHooks()
    {
        add_integration_function('integrate_menu_buttons', 'Dquote::addCopyright', false);
        add_integration_function('integrate_menu_buttons', 'Dquote::loadJS', false);
        add_integration_function('integrate_dquote_notification', 'Dquote::sendNotification', false);
    }

    /**
     * Send notification to quote author if needed
     * @param $msgOptions
     * @param $topicOptions
     * @param $posterOptions
     * @return bool
     */
    public static function sendNotification($msgOptions, $topicOptions, $posterOptions)
    {
        global $txt, $sourcedir, $scripturl, $mbname;
        require_once($sourcedir . '/Subs-Auth.php');

        // Get all quote authors from post
        $quotesCount = preg_match_all('/\[quote\s+author=(.+)\s+link/U', $msgOptions['body'], $quotes);
        if (!$quotesCount) {
            return false;
        }
        $authors = array_unique($quotes[1]);

        // Prepare email
        loadLanguage('Dquote/Dquote');
        $body = $posterOptions['name'] . ' ' . $txt['dQuoteSelection_notify_txt'] . ' ' . $scripturl . '?topic=' . $topicOptions['id'] . '.msg' . $msgOptions['id'] . '#msg' . $msgOptions['id'] . "\n\r\n\r" . $txt['regards_team'];

        $recipients = Dquote::findEmails($authors);
        if (!$recipients) {
            return false;
        }

        // Check recipients
        $emails = array();
        foreach ($recipients as $recipient) {
            if ($recipient['id_member'] == $posterOptions['id']) {
                continue;
            }
            $emails[] = $recipient['email_address'];
        }
        if (!$emails) {
            return false;
        }

        sendmail($emails, $txt['dQuoteSelection_mail_subject_txt'] . ' ' . $mbname, $body);

        return true;
    }

    /**
     * Add mod copyright to the forum credit's page
     */
    public static function addCopyright()
    {
        global $context;

        if ($context['current_action'] == 'credits') {
            $context['copyrights']['mods'][] = '<a href="https://mysmf.net/mods/dquote-selection" target="_blank">dQuoteSelection</a> &copy; 2007-2020, digger';
        }
    }


    /**
     * Load all needed js & css
     */
    public static function loadJS()
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

    /**
     * Find members by email address
     * @param $names
     * @return array
     */
    public static function findEmails($names)
    {
        global $smcFunc;

        // If it's not already an array, make it one.
        if (!is_array($names)) {
            $names = explode(',', $names);
        }

        $request = $smcFunc['db_query'](
            '',
            '
		SELECT id_member, email_address
		FROM {db_prefix}members
		WHERE real_name IN ({array_string:names})
		AND notify_announcements = {int:notify_announcements}',
            array(
                'names'                => $names,
                'notify_announcements' => 1
            )
        );

        $emails = array();
        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            $emails[] = array(
                'id_member'     => $row['id_member'],
                'email_address' => $row['email_address']
            );
        }

        $smcFunc['db_free_result']($request);

        return $emails;
    }
}