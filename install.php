<?php

global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS manager (
	`manager_id` INTEGER NOT NULL PRIMARY KEY $autoincrement,
	`name` VARCHAR( 15 ) NOT NULL ,
	`secret` VARCHAR( 50 ) ,
	`deny` VARCHAR( 255 ) ,
	`permit` VARCHAR( 255 ) ,
	`read` VARCHAR( 50 ) ,
	`write` VARCHAR( 50 )
)";

$check = $db->query($sql);
if (DB::IsError($check)) {
	die_freepbx("Can not create `manager` table" .  $check->getMessage() .  "\n");
}

?>
