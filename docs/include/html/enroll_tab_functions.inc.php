<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2005 by Greg Gay, Joel Kronenberg & Heidi Hazelton*/
/* Adaptive Technology Resource Centre / University of Toronto			*/
/* http://atutor.ca														*/
/*																		*/
/* This program is free software. You can redistribute it and/or		*/
/* modify it under the terms of the GNU General Public License			*/
/* as published by the Free Software Foundation.						*/
/************************************************************************/
if (!defined('AT_INCLUDE_PATH')) { exit; }

$db;

/**
* Generates the tabs for the enroll admin page
* @access  private
* @return  string				The tabs for the enroll_admin page
* @author  Shozub Qureshi
*/
function get_tabs() {
	//these are the _AT(x) variable names and their include file
	/* tabs[tab_id] = array(tab_name, file_name,                accesskey) */
	$tabs[0] = array('enrolled',   'enroll_admin.php', 'e');
	$tabs[1] = array('unenrolled', 'enroll_admin.php', 'u');
	//$tabs[2] = array('assistants', 'enroll_admin.php', 'a');
	$tabs[2] = array('alumni',	   'enroll_admin.php', 'a');

	return $tabs;
}

/**
* Generates the html for the tab action
* @access  private
* @param   int $current_tab		the tab selected currently
* @author  Shozub Qureshi
*/
function output_tabs($current_tab) {
	global $_base_path, $msg;
	$tabs = get_tabs();
	echo '<table cellspacing="0" cellpadding="0" width="92%" border="0" summary="" align="center"><tr>';
	echo '<td>&nbsp;</td>';
	
	$num_tabs = count($tabs);

	for ($i=0; $i < $num_tabs; $i++) {
		if ($current_tab == $i) {

		echo '<td class="etab-selected" width="23%" nowrap="nowrap">';
		echo _AT($tabs[$i][0]).'</td>';

		} else {
			echo '<td class="etab" width="23%">';
			echo '<input type="submit" name="button_'.$i.'" value="'._AT($tabs[$i][0]).'" title="'._AT($tabs[$i][0]).' - alt '.$tabs[$i][2].'" class="buttontab" accesskey="'.$tabs[$i][2].'" onmouseover="this.style.cursor=\'hand\';" '.$clickEvent.' /></td>';
		}
		echo '<td>&nbsp;</td>';
	}	
	echo '</tr></table>';
}

/**
* Generates the html for the enrollment tables
* @access  private
* @param   string $condition	the condition to be imposed in the sql query (approved = y/n/a)
* @param   string $col			the column to be sorted
* @param   string $order		the sorting order (DESC or ASC)
* @param   int $unenr			is one if the unenrolled list is being generated
* @author  Shozub Qureshi
* @author  Joel Kronenberg
*/
function generate_table($condition, $col, $order, $unenr, $view_select=0) {
	global $db;

	if ($view_select == -1) {
		$condition .= ' AND CE.privileges<>0';
	} else if ($view_select > 0) {
		$sql = "SELECT member_id FROM ".TABLE_PREFIX."groups_members WHERE group_id=$view_select";
		$result = mysql_query($sql, $db);
		while ($row = mysql_fetch_assoc($result)) {
			$members_list .= ',' . $row['member_id'];
		}
		$condition .= ' AND CE.member_id IN (0'.$members_list.')';
	}
	//output list of enrolled students
	$sql	=  "SELECT CE.member_id, CE.role, M.login, M.first_name, M.last_name, M.email, M.confirmed 
				FROM ".TABLE_PREFIX."course_enrollment CE, ".TABLE_PREFIX."members M 
				WHERE CE.course_id=$_SESSION[course_id] AND CE.member_id=M.member_id AND ($condition) 
				ORDER BY $col $order";
	$result	= mysql_query($sql, $db);
	
	echo '<tbody>';
	//if table is empty display message
	if (mysql_num_rows($result) == 0) {
		echo '<tr><td align="center" colspan="6">'._AT('empty').'</td></tr>';
	} else {
		while ($row  = mysql_fetch_assoc($result)) {
			echo '<tr onmousedown="document.selectform[\'m' . $row['member_id'] . '\'].checked = !document.selectform[\'m' . $row['member_id'] . '\'].checked;">';
			echo '<td>';

			$act = "";
			if ($row['member_id'] == $_SESSION['member_id']) {
				$act = 'disabled="disabled"';	
			} 
			
			echo '<input type="checkbox" name="id[]" value="'.$row['member_id'].'" id="m'.$row['member_id'].'" ' . $act . ' onmouseup="this.checked=!this.checked" />';			
			echo AT_print($row['login'], 'members.login') . '</td>';
			echo '<td>' . AT_print($row['first_name'], 'members.name') . '</td>';
			echo '<td>' . AT_print($row['last_name'], 'members.name')  . '</td>';
			echo '<td>' . AT_print($row['email'], 'members.email') . '</td>';
			
			//if role not already assigned, assign role to be student
			//and we are not vieiwing list of unenrolled students
			echo '<td>';
			if ($row['role'] == '' && $unenr != 1) {
				echo _AT('Student');
			} else if ($unenr == 1) {
				echo _AT('na');
			} else {
				echo AT_print($row['role'], 'members.role');
			}
			echo '</td>';
			echo '<td>';
			if ($row['confirmed']) {
				echo _AT('yes');
			} else {
				echo _AT('no');
			}
			echo '</td>';
			echo '</tr>';
		}		
	}
	echo '</tbody>';
}

/**
* Generates the html for the SORTED enrollment tables
* @access  private
* @param   int $curr_tab	the current tab (enrolled, unenrolled or alumni)
* @author  Shozub Qureshi
*/
function display_columns ($curr_tab) {
?>
	<th scope="col"><input type="checkbox" value="<?php echo _AT('select_all'); ?>" id="all" title="<?php echo _AT('select_all'); ?>" name="selectall" onclick="CheckAll();" /><?php echo _AT('login'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=login<?php echo SEP; ?>order=asc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('login_ascending'); ?>"><img src="images/asc.gif" alt="<?php echo _AT('login_ascending'); ?>" border="0" height="7" width="11" /></a> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=login<?php echo SEP; ?>order=desc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('login_descending'); ?>"><img src="images/desc.gif" alt="<?php echo _AT('login_descending'); ?>" border="0" height="7" width="11" /></a></th>

	<th scope="col"><?php echo _AT('first_name'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=first_name<?php echo SEP; ?>order=asc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('first_name_ascending'); ?>"><img src="images/asc.gif" alt="<?php echo _AT('first_name_ascending'); ?>" border="0" height="7" width="11" /></a> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=first_name<?php echo SEP; ?>order=desc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('first_name_descending'); ?>"><img src="images/desc.gif" alt="<?php echo _AT('first_name_descending'); ?>" border="0" height="7" width="11" /></a></th>

	<th scope="col"><?php echo _AT('last_name'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=last_name<?php echo SEP; ?>order=asc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('last_name_ascending'); ?>"><img src="images/asc.gif" alt="<?php echo _AT('last_name_ascending'); ?>" border="0" height="7" width="11" /></a> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=last_name<?php echo SEP; ?>order=desc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('last_name_descending'); ?>"><img src="images/desc.gif" alt="<?php echo _AT('last_name_descending'); ?>" border="0" height="7" width="11" /></a></th>

	<th scope="col"><?php echo _AT('email'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=email<?php echo SEP; ?>order=asc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('email_ascending'); ?>"><img src="images/asc.gif" alt="<?php echo _AT('email_ascending'); ?>" border="0" height="7" width="11" /></a> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=email<?php echo SEP; ?>order=desc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('email_descending'); ?>"><img src="images/desc.gif" alt="<?php echo _AT('email_descending'); ?>" border="0" height="7" width="11" /></a></th>

	<th scope="col"><?php echo _AT('role'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=role<?php echo SEP; ?>order=desc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('role_ascending'); ?>"><img src="images/asc.gif" alt="<?php echo _AT('role_ascending'); ?>" border="0" height="7" width="11" /></a> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?col=role<?php echo SEP; ?>order=asc<?php echo SEP; ?>current_tab=<?php echo $curr_tab; ?>" title="<?php echo _AT('role_descending'); ?>"><img src="images/desc.gif" alt="<?php echo _AT('role_descending'); ?>" border="0" height="7" width="11" /></a></th>

	<th scope="col"><?php echo _AT('confirmed'); ?></th>
<?php	
}

?>