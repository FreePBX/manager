<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
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
global $amp_conf;
$view = isset($_REQUEST['view'])?$_REQUEST['view']:'';
switch($view){
	case 'form':
		$content = load_view(__DIR__.'/views/form.php');
	break;
	default:
		$content = load_view(__DIR__.'/views/grid.php');
	break;
}
?>
<div class="container-fluid">
	<div class="col-sm-9">
		<h1><?php echo _('Asterisk Manager')?></h1>
		<div class="alert alert-info">
			<?php
				$fileData = @parse_ini_file($amp_conf['ASTETCDIR'].'/manager.conf', false);
				echo _('AMI current settings for Bind Address : ' . $fileData['bindaddr'] .' and bind port : ' . $fileData['port'] .'.');
			 ?>
		</div>
	</div>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-9">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<div class="col-sm-3 hidden-xs bootnav <?php echo (isset($_REQUEST['view'])?'':'hidden')?>">
				<div class="list-group">
					<a href="?display=manager" class="list-group-item"><i class="fa fa-list"></i> <?php echo _("List Managers")?></a>
					<a href="?type=tool&display=manager&view=form" class="list-group-item <?php echo (isset($_REQUEST['managerdisplay'])?'':'hidden')?>"><i class="fa fa-plus"></i> <?php echo _("Add Manager")?></a>
				</div>
			</div>
		</div>
	</div>
</div>
