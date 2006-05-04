<?php
// Xavier Ourciere xourciere[at]propolys[dot]com
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying
isset($_REQUEST['managerdisplay'])?$managerdisplay=$_REQUEST['managerdisplay']:$managerdisplay='';
$dispnum = "manager"; //used for switch on config.php

//if submitting form, update database
switch ($action) {
	case "add":
		manager_add($_REQUEST['name'],$_REQUEST['secret'],$_REQUEST['deny'],$_REQUEST['permit'],$_REQUEST['read'],$_REQUEST['write']);
		manager_gen_conf();
		needreload();
	break;
	case "delete":
		manager_del($managerdisplay);
		manager_gen_conf();
		needreload();
	break;
	case "edit":  //just delete and re-add
		manager_del($_REQUEST['name']);
		manager_add($_REQUEST['name'],$_REQUEST['secret'],$_REQUEST['deny'],$_REQUEST['permit'],$_REQUEST['read'],$_REQUEST['write']);
		manager_gen_conf();
		needreload();
	break;
}

$managers = manager_list();
?>

</div>

<!-- right side menu -->
<div class="rnav">
    <li><a id="<?php echo ($managerdisplay=='' ? 'current':'') ?>" href="config.php?type=tool&amp;display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Manager")?></a></li>
<?php
if (isset($managers)) {
	foreach ($managers as $manager) {
		echo "<li><a id=\"".($managerdisplay==$manager['name'] ? 'current':'')."\" href=\"config.php?type=tool&amp;display=".urlencode($dispnum)."&managerdisplay=".$manager['name']."\">{$manager['name']}</a></li>";
	}
}
?>
</div>


<div class="content">
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Manager").' '.$managerdisplay.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($managerdisplay){ 
		//get details for this manager
		$thisManager = manager_get($managerdisplay);
		//create variables
		extract($thisManager);
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
?>

	
<?php		if ($managerdisplay){ ?>
	<h2><?php echo _("Manager:")." ". $managerdisplay; ?></h2>
	<p><a href="<?php echo $delURL ?>"><?php echo _("Delete Manager")?> <?php echo $managerdisplay; ?></a></p>
<?php		} else { ?>
	<h2><?php echo _("Add Manager"); ?></h2>
<?php		}
?>
	<form autocomplete="off" name="editMan" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkConf();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($managerdisplay ? 'edit' : 'add') ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo ($managerdisplay ? _("Edit Manager") : _("Add Manager")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("manager name:")?><span><?php echo _("Name of the manager without space.")?></span></a></td>
		<td><input type="text" name="name" value="<?php echo (isset($name) ? $name : ''); ?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("manager secret:")?><span><?php echo _("Password for the manager.")?></span></a></td>
		<td><input type="text" name="secret" value="<?php echo (isset($secret) ? $secret : ''); ?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("deny:")?><span><?php echo _("If you want to deny many hosts or networks, use & char as separator.<br/><br/>Example: 192.168.1.0/255.255.255.0&10.0.0.0/255.0.0.0")?></span></a></td>
		<td><input size="36" type="text" name="deny" value="<?php echo (isset($deny) ? $deny : ''); ?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("permit:")?><span><?php echo _("If you want to permit many hosts or networks, use & char as separator. Look at deny example.")?></span></a></td>
		<td><input size="36" type="text" name="permit" value="<?php echo (isset($permit) ? $permit : ''); ?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("read:")?><span><?php echo _("Can be one or all of these separated with a comma:<br/><b>system,call,log,verbose,command,agent,user</b>.")?></span></a></td>
		<td><input size="36" type="text" name="read" value="<?php echo (isset($read) ? $read : ''); ?>"></td>
	</tr>

	<tr>
		<td><a href="#" class="info"><?php echo _("write:")?><span><?php echo _("Can be one or all of these separated with a comma:<br/><b>system,call,log,verbose,command,agent,user</b>.")?></span></a></td>
		<td><input size="36" type="text" name="write" value="<?php echo (isset($write) ? $write : ''); ?>"></td>
	</tr>
						   
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>"></h6></td>		
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.editMan;

theForm.name.focus();

function checkConf()
{
	var errName = "<?php echo _('The manager name cannot be empty or may not have any space in it.'); ?>";
	var errSecret = "<?php echo _('The manager secret cannot be empty.'); ?>";
	var errReadWrite = "<?php echo _('The manager read and write properties cannot be empty.'); ?>";
	var errDeny = "<?php echo _('The manager deny is not well formated.'); ?>";
	var errPermit = "<?php echo _('The manager permit is not well formated.'); ?>";
	var errRead = "<?php echo _('The manager read field is not well formated.'); ?>";
	var errWrite = "<?php echo _('The manager write field is not well formated.'); ?>";

	defaultEmptyOK = false;
	if ((theForm.name.value.search(/\s/) >= 0) || (theForm.name.value.length == 0))
		return warnInvalid(theForm.name, errName);
	if (theForm.secret.value.length == 0)
		return warnInvalid(theForm.name, errSecret);
	if ((theForm.read.value.length == 0) || (theForm.write.value.length == 0))
		return warnInvalid(theForm.name, errReadWrite);
	// Only IP/MASK format are checked
	if (theForm.deny.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
		return warnInvalid(theForm.name, errDeny);
	if (theForm.permit.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
		return warnInvalid(theForm.name, errPermit);
	if (theForm.read.value.search(/\b(system|call|log|verbose|command|agent|user)\b(,\b(system|call|log|verbose|command|agent|user)\b)*$/))
		return warnInvalid(theForm.name, errRead);
	if (theForm.write.value.search(/\b(system|call|log|verbose|command|agent|user)\b(,\b(system|call|log|verbose|command|agent|user)\b)*$/))
		return warnInvalid(theForm.name, errWrite);
		
	return true;
}

//-->
</script>
	</form>
<?php		
} //end if action == delGRP
?>
