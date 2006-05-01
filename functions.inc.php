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

// Delete a manager
function manager_del($p_name) {
	$results = sql("DELETE FROM manager WHERE name = \"$p_name\"","query");
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
