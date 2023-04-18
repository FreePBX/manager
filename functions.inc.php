<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Asterisk API Module hooking
// Input:
//   $p_manager = default selected user
//   $dummy = unused
// $viewing_itemid, $target_menuid
function manager_hook_phpagiconf($viewing_itemid, $target_menuid) {

	//TODO: Is this code still used or is it obsolete code?
	global $db;

	switch($target_menuid) {
		case 'phpagiconf':
			$sql = "SELECT asman_user FROM phpagiconf";
			$res = $db->getRow($sql, DB_FETCHMODE_ASSOC);
			if(DB::IsError($res)) {
				return null;
			}
			$selectedmanager = $res['asman_user'];
		break;
	}
	$output = "<tr><td><a href=\"#\" class=\"info\">"._("Choose Manager:")."<span>"._("Choose the user that PHPAGI will use to connect the Asterisk API.")."</span></a></td><td><select name=\"asmanager\">";
	$selected = "";
	$managers = manager_list();
	foreach ($managers as $manager) {
		($manager['name'] === $selectedmanager) ? $selected="selected=\"selected\"" : $selected="";
		$output .= "<option value=\"".$manager['name']."/".$manager['secret']."\" $selected>".$manager['name'];
	}
	$output .="</select></td></tr>";
	return $output;
}


// Get the manager list
function manager_list()
{
	\FreePBX::Modules()->deprecatedFunction();
	return \FreePBX::Manager()->list_managers();
}

// Get manager infos
function manager_get($p_name)
{
	\FreePBX::Modules()->deprecatedFunction();
	try
	{
		$data_return = \FreePBX::Manager()->get_manager($p_name, true);
	}
	catch (\Exception $e)
	{
		$data_return = array();
	}
	return $data_return;
}

// Used to set the correct values for the html checkboxes
function manager_format_out($p_tab)
{
	\FreePBX::Modules()->deprecatedFunction();
	return \FreePBX::Manager()->format_out($p_tab);
}

// Delete a manager
function manager_del($p_name) {
	\FreePBX::Modules()->deprecatedFunction();
	try
	{
		$data_return = \FreePBX::Manager()->del_manager($p_name, true);
	}
	catch (\Exception $e)
	{
		$data_return = false;
	}
	return $data_return;
}

function manager_format_in($p_tab)
{
	\FreePBX::Modules()->deprecatedFunction();
	return \FreePBX::Manager()->format_in($p_tab);
}

// Add a manager
function manager_add($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write, $p_writetimeout=100)
{
	\FreePBX::Modules()->deprecatedFunction();
	try
	{
		\FreePBX::Manager()->add_manager($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write, $p_writetimeout);
	}
	catch (\Exception $e)
	{
		echo sprintf("<script>javascript:alert('%s');</script>", $e->getMessage());
		return false;
	}
}
