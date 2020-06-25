<?php

global $boardurl;

$txt['dQuoteSelection_txt']               = 'Quote (selected)';
$txt['dQuoteSelection_notify_type']       = 'Receive notification when a post of mine is quoted:';
$txt['dQuoteSelection_notify_type_none']  = 'none';
$txt['dQuoteSelection_notify_type_email'] = 'by e-mail';
$txt['dQuoteSelection_mail_subject']      = 'You have been quoted in the post: ';
$txt['dQuoteSelection_mail_body']         = 'Hello, %s!

%s quoted you in the post titled "%s".

You can see the post here:
%s

Unsubscribe from quote notifications by using this link: ' . $boardurl . '?action=profile;area=notification';
