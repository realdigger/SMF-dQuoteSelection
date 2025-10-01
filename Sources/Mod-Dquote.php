<?php
/**
 * Project: dQuote Selection
 * Version: 2.7.4
 * File: Mod-dQuote.php
 * Author: digger @ https://mysmf.ru
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
        add_integration_function('integrate_menu_buttons', __CLASS__ . '::addCopyright', false);
        add_integration_function('integrate_menu_buttons', __CLASS__ . '::loadJS', false);
        add_integration_function('integrate_profile_areas', __CLASS__ . '::addSettings', false);
        add_integration_function('integrate_dquote_notification', __CLASS__ . '::sendNotification', false);
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
        global $txt, $scripturl, $context;

        // Get all quote authors from post
        $quotesCount = preg_match_all('/\[quote\s+author=(.+)\s+link/U', $msgOptions['body'], $quotes);
        if (!$quotesCount) {
            return false;
        }
        $authors = array_unique($quotes[1]);

        // Get recipients
        $recipients = Dquote::findRecipients($authors);
        if (!$recipients) {
            return false;
        }

        // Prepare emails
        loadLanguage('Dquote/Dquote');
        $subject = $txt['dQuoteSelection_mail_subject'] . ' ' . $msgOptions['subject'];

        // Send mails
        foreach ($recipients as $recipient) {
            // Check recipient
            if ($recipient['id_member'] == $posterOptions['id'] || $recipient['dquote_notify_type'] != 'email') {
                continue;
            }

            $body = sprintf(
                    $txt['dQuoteSelection_mail_body'],
                    $recipient['real_name'],
                    $context['user']['name'],
                    $msgOptions['subject'],
                    $scripturl . '?topic=' . $topicOptions['id'] . '.msg' . $msgOptions['id'] . '#msg' . $msgOptions['id']
                ) . "\n\r\n\r" . $txt['regards_team'];

            sendmail($recipient['email_address'], $subject, $body);
        }

        return true;
    }

    /**
     * Add mod copyright to the forum credit's page
     * @return void
     */
    public static function addCopyright()
    {
        global $context;

        if ($context['current_action'] == 'credits') {
            $context['copyrights']['mods'][] = '<a href="https://mysmf.net/mods/dquote-selection" target="_blank">dQuote Selection and Notification</a> &copy; 2007-2025, digger';
        }
    }


    /**
     * Load all needed js & css
     * @return void
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
        <script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/dquote.js?274"></script>';
    }

    /**
     * Find members by email address
     * @param $names
     * @return array
     */
    private static function findRecipients($names)
    {
        global $smcFunc;
        $recipients = [];

        // If it's not already an array, make it one.
        if (!is_array($names)) {
            $names = explode(',', $names);
        }

        $request = $smcFunc['db_query'](
            '',
            '
		SELECT m.id_member, m.email_address, m.real_name, t.value AS dquote_notify_type
		FROM {db_prefix}members m
		LEFT JOIN {db_prefix}themes t ON 
		    t.id_member = m.id_member AND 
		    t.id_theme = {int:id_theme} AND 
		    t.variable = {string:variable}
		WHERE real_name IN ({array_string:names})',
            [
                'names'    => $names,
                'id_theme' => 1,
                'variable' => 'dquote_notify_type'
            ]
        );

        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            $recipients[] = [
                'id_member'          => $row['id_member'],
                'email_address'      => $row['email_address'],
                'real_name'          => $row['real_name'],
                'dquote_notify_type' => !empty($row['dquote_notify_type']) ? $row['dquote_notify_type'] : 'email',
            ];
        }

        $smcFunc['db_free_result']($request);

        return $recipients;
    }


    /**
     * Add profile settings
     * @return void
     */
    public static function addSettings()
    {
        global $context, $txt, $options, $sourcedir;

        if (!empty($context['user']['id']) && !empty($_GET['area']) && $_GET['area'] == 'notification' && $context['user']['is_owner']) {
            require_once($sourcedir . '/Profile-Modify.php');
            loadLanguage('Dquote/Dquote');

            // Set default option to email
            if (empty($options['dquote_notify_type'])) {
                $options['dquote_notify_type'] = 'email';
            }

            $txt['notify_send_type_nothing'] .= '</option>
                        </select>
                        <br /><br />
                        <label for="dquote_notify_type">' . $txt['dQuoteSelection_notify_type'] . '</label>
                        <select name="options[dquote_notify_type]" id="dquote_notify_type">
		    		        	<option value="email"' . ($options['dquote_notify_type'] == 'email' ? ' selected' : '') . '>' . $txt['dQuoteSelection_notify_type_email'] . '</option>                                
	    				        <option value="none"' . ($options['dquote_notify_type'] == 'none' ? ' selected' : '') . '>' . $txt['dQuoteSelection_notify_type_none'];

            makeThemeChanges($context['user']['id'], 1);
        } else {
            return;
        }
    }
}
