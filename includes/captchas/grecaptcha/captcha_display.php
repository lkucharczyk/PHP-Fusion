<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
| Author: skpacman
| Copyright 2015 Stephen D King Jr
| ------------------------------------------------------
| This integrates the NEW reCAPTCHA Google API v2 into
| PHP-Fusion using the built-in PHP-Fusion captcha system
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
include LOCALE.LOCALESET."user_fields/user_grecaptcha.php";
//include_once INCLUDES."captchas/grecaptcha/functions.php";
$_CAPTCHA_HIDE_INPUT = true;
add_to_head("<script type='text/javascript' src='https://www.google.com/recaptcha/api.js?hl=".$locale['xml_lang']."'></script>");
echo "<div class='g-recaptcha' data-theme='".fusion_get_settings("recaptcha_theme")."' data-sitekey='".fusion_get_settings("recaptcha_public")."'></div>\n";
add_to_jquery("
	$('.g-recaptcha').hide();
	$('.g-recaptcha').delay(".fusion_get_settings("recaptcha_time")."000).fadeIn(1000);
	$('.loading_container').show();
	$('.loading_container').delay(".fusion_get_settings("recaptcha_time")."000).fadeOut(0);
");