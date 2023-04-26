<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<div id="toolbar-all">
    <a href="#" class="btn btn-default" data-toggle="modal" data-target="#managerForm"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo _("Add Manager") ?></a>
</div>
<table
    id="managersgrid"
    data-url="ajax.php?module=manager&command=list"
    data-cache="false"
    data-toolbar="#toolbar-all"
    data-show-columns="true"
    data-toggle="table"
    data-pagination="true"
    data-show-refresh="true"
    data-search="true"
    data-resizable="true"
	data-sortable="true"
    class="table table-striped">
    <thead>
        <tr>
            <th data-field="name" data-sortable="true" class="col-md-3">
                <?php echo _("Name")?>
            </th>
            <th data-field="deny" data-sortable="true" class="col-md-4">
                <?php echo _("Deny")?>
            </th>
            <th data-field="permit" data-sortable="true" class="col-md-4">
                <?php echo _("Permit")?>
            </th>
            <th data-field="manager_id" data-formatter="linkFormatter" class="col-md-1 text-center">
                <?php echo _("Actions")?>
            </th>
        </tr>
    </thead>
</table>