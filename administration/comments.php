<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: comments.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('C');

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/comments.php";

if (!isset($_GET['ctype']) || !preg_check("/^[0-9A-Z]+$/i", $_GET['ctype'])) {
	redirect("../index.php");
}

if (!isset($_GET['comment_item_id']) || !isnum($_GET['comment_item_id'])) {
	redirect("../index.php");
}

$message = '';
if (isset($_GET['status'])) {
	switch($_GET['status']) {
		case 'su':
			$message = $locale['421'];
			$status = 'info';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'del':
			$message = $locale['411'];
			$status = 'danger';
			$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
			break;
	}
	if ($message) {
		addNotice($status, $icon.$message);
	}
}

if (isset($_POST['save_comment']) && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
	$comment_message = stripinput($_POST['comment_message']);
	$result = dbquery("UPDATE ".DB_COMMENTS." SET comment_message='$comment_message' WHERE comment_id='".$_GET['comment_id']."'");
	redirect("comments.php".$aidlink."&ctype=".$_GET['ctype']."&comment_item_id=".$_GET['comment_item_id']."&status=su");
}

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
	$result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id='".$_GET['comment_id']."'");
	redirect("comments.php".$aidlink."&ctype=".$_GET['ctype']."&comment_item_id=".$_GET['comment_item_id']."&status=del");
}

if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
	$result = dbquery("SELECT comment_message FROM ".DB_COMMENTS." WHERE comment_id='".$_GET['comment_id']."'");
	if (dbrows($result)) {
		require_once INCLUDES."bbcode_include.php";
		$data = dbarray($result);
		opentable($locale['400']);
		$form_action = FUSION_SELF.$aidlink."&amp;comment_id=".$_GET['comment_id']."&amp;ctype=".$_GET['ctype']."&amp;comment_item_id=".$_GET['comment_item_id'];
		echo openform('settingsform', 'post', $form_action, array('max_tokens' => 1));
		echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
		echo "<td align='center' class='tbl'>\n";
		echo form_textarea('comment_message', '', $data['comment_message']);
		echo "</td></tr>\n<tr>\n";
		echo "<td align='center' class='tbl'><input type='submit' name='save_comment' value='".$locale['421']."' class='button' /></td>\n";
		echo "</tr>\n</table>\n";
		closeform();
		closetable();
	}
}

opentable($locale['401']);
$i = 0;
$result = dbquery("SELECT c.comment_id, c.comment_name, c.comment_message, c.comment_datestamp, c.comment_ip, u.user_id, u.user_name, u.user_status FROM ".DB_COMMENTS." c
	LEFT JOIN ".DB_USERS." u
	ON c.comment_name=u.user_id
	WHERE c.comment_type='".$_GET['ctype']."' AND c.comment_item_id='".$_GET['comment_item_id']."' ORDER BY c.comment_datestamp ASC");
if (dbrows($result)) {
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border center'>\n";
	while ($data = dbarray($result)) {
		echo "<tr>\n<td class='".($i%2 == 0 ? "tbl1" : "tbl2")."'><span class='comment-name'>";
		if ($data['user_name']) {
			echo "<span class='slink'>".profile_link($data['comment_name'], $data['user_name'], $data['user_status'])."</span>";
		} else {
			echo $data['comment_name'];
		}
		echo "</span>\n<span class='small'>".$locale['global_071'].showdate("longdate", $data['comment_datestamp'])."</span><br />\n";
		echo nl2br(parseubb(parsesmileys($data['comment_message'])))."<br />\n";
		echo "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;comment_id=".$data['comment_id']."&amp;ctype=".$_GET['ctype']."&amp;comment_item_id=".$_GET['comment_item_id']."'>".$locale['430']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;comment_id=".$data['comment_id']."&amp;ctype=".$_GET['ctype']."&amp;comment_item_id=".$_GET['comment_item_id']."' onclick=\"return confirm('".$locale['433']."');\">".$locale['431']."</a> -\n";
		echo "<strong>".$locale['432']." ".$data['comment_ip']."</strong></span>\n";
		echo "</td>\n</tr>\n";
		$i++;
	}
	echo "</table>\n";
} else {
	echo "<div style='text-align:center'><br />".$locale['434']."<br /><br /></div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
