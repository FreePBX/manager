<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<div class="container-fluid">
	<div class="col-sm-12">
		<h1><?php echo _('Asterisk Manager')?></h1>
		<div class="alert alert-info">
			<?php
				echo "<p>"._("The default Asterisk Manager Interface (AMI) configuration has changed starting in version 16. ");
				echo sprintf( _('Please refer to  <a href="%s" target="_blank"> <b>wiki</b> </a> for more information.'), "https://wiki.sangoma.com/display/FPG/AMI+Default+Configuration+in+16")."</p>";
				echo "<p>".sprintf( _('AMI current settings for Bind Address : %s and bind port : %s.'), $manager->readConfig('bindaddr'), $manager->readConfig('port'))."</p>";
			 ?>
		</div>
	</div>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $manager->showPage('grid'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>