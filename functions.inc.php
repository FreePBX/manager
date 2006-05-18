<?php

function manager_gen_conf() {
	$file = "/tmp/manager_additional_".rand().".conf";
	$content = "";
	$managers = manager_list();
	if (is_array($managers)) {
		foreach ($managers as $manager) {
			$res = manager_get($manager['name']);
			$content .= "[".$res['name']."]\n";
			$content .= "secret = ".$res['secret']."\n";
			$tmp = explode("&", $res['deny']);
			foreach ($tmp as $item) {
				$content .= "deny=$item\n";
			}
			$tmp = explode("&", $res['permit']);
			foreach ($tmp as $item) {
				$content .= "permit=$item\n";
			}
			$content .= "read = ".$res['read']."\n";
			$content .= "write = ".$res['write']."\n";
			$content .= "\n";
		}
	}
	$fd = fopen($file, "w");
	fwrite($fd, $content);
	fclose($fd);
	if (!rename($file, "/etc/asterisk/manager_additional.conf")) {
		echo "<script>javascript:alert('"._("Error writing the manager additional file.")."');</script>";
	}
}

// Get the manager list
function manager_list() {
	global $db;
	$sql = "SELECT name FROM manager ORDER BY name";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($res)) {
		return null;
	}
	return $res;
}

// Get manager infos
function manager_get($p_name) {
	global $db;
	$sql = "SELECT name,secret,deny,permit,`read`,`write` FROM manager WHERE name = '$p_name'";
	$res = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	return $res;
}

// Used to set the correct values for the html checkboxes
function manager_format_out($p_tab) {
	$res['name'] = $p_tab['name'];
	$res['secret'] = $p_tab['secret'];
	$res['deny'] = $p_tab['deny'];
	$res['permit'] = $p_tab['permit'];

	$tmp = explode(',', $p_tab['read']);
	foreach($tmp as $item) {
		$res['r'.$item] = true;
	}

	$tmp = explode(',', $p_tab['write']);
	foreach($tmp as $item) {
		$res['w'.$item] = true;
	}

	return $res;
}

// Delete a manager
function manager_del($p_name) {
	$results = sql("DELETE FROM manager WHERE name = \"$p_name\"","query");
}

function manager_format_in($p_tab) {
	if (isset($p_tab['rsystem']))
		$res['read'] .= "system,";
	if (isset($p_tab['rcall']))
		$res['read'] .= "call,";
	if (isset($p_tab['rlog']))
		$res['read'] .= "log,";
	if (isset($p_tab['rverbose']))
		$res['read'] .= "verbose,";
	if (isset($p_tab['rcommand']))
		$res['read'] .= "command,";
	if (isset($p_tab['ragent']))
		$res['read'] .= "agent,";
	if (isset($p_tab['ruser']))
		$res['read'] .= "user";

	if (isset($p_tab['wsystem']))
		$res['write'] .= "system,";
	if (isset($p_tab['wcall']))
		$res['write'] .= "call,";
	if (isset($p_tab['wlog']))
		$res['write'] .= "log,";
	if (isset($p_tab['wverbose']))
		$res['write'] .= "verbose,";
	if (isset($p_tab['wcommand']))
		$res['write'] .= "command,";
	if (isset($p_tab['wagent']))
		$res['write'] .= "agent,";
	if (isset($p_tab['wuser']))
		$res['write'] .= "user";
	
	return $res;
}

// Add a manager
function manager_add($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write) {
	$managers = manager_list();
	if (is_array($managers)) {
		foreach ($managers as $manager) {
			if ($manager['name'] === $p_name) {
				echo "<script>javascript:alert('"._("This manager already exists")."');</script>";
				return false;
			}
		}
	}
	$results = sql("INSERT INTO manager set name='$p_name' , secret='$p_secret' , deny='$p_deny' , permit='$p_permit' , `read`='$p_read' , `write`='$p_write'");
}
?>
