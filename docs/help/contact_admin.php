<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2003 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/

	$page = 'help';
	$_user_location	= 'users';

	define('AT_INCLUDE_PATH', '../include/');
	require(AT_INCLUDE_PATH.'vitals.inc.php');

	$_section[0][0] = _AT('help');
	$_section[0][1] = 'help/';
	$_section[1][0] = _AT('contact_admin');

	if ($_POST['cancel']) {
		Header('Location: index.php?cid='.$_POST['pid'].SEP.'f='.urlencode_feedback(AT_FEEDBACK_CANCELLED));
		exit;
	}

	require (AT_INCLUDE_PATH.'header.inc.php');

	if (!get_instructor_status( )) {
		$errors[] = AT_ERROR_ACCESS_INSTRUCTOR;
		print_errors($errors);
		require(AT_INCLUDE_PATH.'footer.inc.php');
		exit;
	}

	$sql	= "SELECT first_name, last_name, email FROM ".TABLE_PREFIX."members WHERE member_id=$_SESSION[member_id]";
	$result = mysql_query($sql, $db);
	if ($row = mysql_fetch_array($result)) {
		$student_name = AT_print($row['last_name'], 'members.last_name');
		$student_name .= (AT_print($row['first_name'], 'members.first_name') ? ', '.AT_print($row['first_name'], 'members.first_name') : '');

		$student_email = AT_print($row['email'], 'members.email');
	} else {
		$errors[]=AT_ERROR_STUD_INFO_NOT_FOUND;
		print_errors($errors);
		require(AT_INCLUDE_PATH.'footer.inc.php');
		exit;
	}

	if (!defined('ADMIN_EMAIL')) {
		$errors[]=AT_ERROR_ADMIN_INFO_NOT_FOUND;
		print_errors($errors);
		require(AT_INCLUDE_PATH.'footer.inc.php');
		exit;
	}

	echo '<h2>';
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 2) {
		echo '<img src="images/icons/default/square-large-help.gif" width="42" hspace="3" vspace="3" height="38"  class="menuimage" border="0" alt="" />';
	}
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 1) {
		echo '<a href="help/">'._AT('help').'</a>';
	}
	echo '</h2>';
	if ($_POST['submit']) {
		$_POST['subject'] = trim($_POST['subject']);
		$_POST['body']	  = trim($_POST['body']);

		if ($_POST['subject'] == '') {
			$errors[] = AT_ERROR_MSG_SUBJECT_EMPTY;
		}
		
		if ($_POST['body'] == '') {
			$errors[] = AT_ERROR_MSG_BODY_EMPTY;
		}
		
		if (!$errors) {

			require(AT_INCLUDE_PATH . 'classes/phpmailer/atutormailer.class.php');

			$mail = new ATutorMailer;

			$mail->From     = $_POST['from_email'];
			$mail->AddAddress(ADMIN_EMAIL);
			$mail->Subject = $_POST['subject'];
			$mail->Body    = $_POST['body'];

			if(!$mail->Send()) {
			   echo 'There was an error sending the message';
			   exit;
			}
			unset($mail);
		
			print_feedback(_AT('message_sent'));
			require(AT_INCLUDE_PATH.'footer.inc.php');
			exit;
		}
	}

	print_errors($errors);

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table cellspacing="1" cellpadding="0" border="0" class="bodyline" width="85%" summary="" align="center">
<tr>
	<th colspan="2" align="left" class="left"><?php echo _AT('contact_admin_form') ;?></th>
</tr>
<tr>
	<td class="row1" align="right"><b><?php echo _AT('to_name'); ?>:</b></td>
	<td class="row1"><?php echo _AT('atutor_sys_admin'); ?></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="right"><b><?php echo _AT('to_email'); ?>:</b></td>
	<td class="row1"><i><?php echo _AT('hidden'); ?></i></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="right"><label for="from"><b><?php echo _AT('from_name'); ?>:</b></label></td>
	<td class="row1"><input type="text" class="formfield" name="from" id="from" size="40" value="<?php echo $student_name;?>" /></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="right"><label for="from_email"><b><?php echo _AT('from_email'); ?>:</b></label></td>
	<td class="row1"><input type="text" class="formfield" name="from_email" id="from_email" size="40" value="<?php echo $student_email;?>" /></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="right"><label for="subject"><b><?php echo _AT('subject'); ?>:</b></label></td>
	<td class="row1"><input type="text" class="formfield" name="subject" id="subject" size="40" value="<?php echo $_POST['subject']; ?>" /></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="right" valign="top"><label for="body"><b><?php echo _AT('body'); ?>:</b></label></td>
	<td class="row1"><textarea class="formfield" cols="55" rows="15" id="body" name="body"><?php echo $_POST['body']; ?></textarea><br /><br /></td>
</tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr><td height="1" class="row2" colspan="2"></td></tr>
<tr>
	<td class="row1" align="center" colspan="2"><input type="submit" name="submit" class="button" value="<?php echo _AT('send_message'); ?> [Alt-s]" accesskey="s" /> - <input type="submit" name="cancel" class="button" value="<?php echo _AT('cancel'); ?>" /></td>
</tr>
</table>

</form>
<br />

<?php
	require(AT_INCLUDE_PATH.'footer.inc.php');
?>