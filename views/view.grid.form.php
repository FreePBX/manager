<?php 
    if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

    $permtypes = $manager->getPermissions();
?>

<!--Add Modal -->
<div class="modal fade" id="managerForm" tabindex="-1" role="dialog" aria-labelledby="managerForm" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo _("Loading...") ?></h4>
            </div>
            <div class="modal-body">
                <form class="fpbx-submit" name="editManager" id="editManager" action="" autocomplete="off" role="form">
                    <input type="hidden" id="idManager" value="" />

                    <ul class="nav nav-pills" role="tablist">
                        <li data-name="managerset" class="change-tab active">
                            <a href="#managerset" aria-controls="managerset" role="tab" data-toggle="tab"><i class="fa fa-cog fa-lg" aria-hidden="true"></i> <?php echo _("General")?></a>
                        </li>
                        <li data-name="managerperm" class="change-tab">
                            <a href="#managerperm" aria-controls="managerperm" role="tab" data-toggle="tab"><i class="fa fa-lock fa-lg" aria-hidden="true"></i> <?php echo _("Permissions")?></a>
                        </li>
                    </ul>
                    <div class="tab-content display no-border">
                        <!-- Tab General -->
                        <div id="managerset" class="tab-pane active">
                            <!--Manager name INI-->
                            <div class="element-container" style="margin-top: 1rem;">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3 col-lg-3 col-sm-12">
                                            <label class="control-label" for="nameManager"><?php echo _("Name") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="nameManager"></i>
                                        </div>
                                        <div class="col-md-9 col-lg-9 col-sm-12">
                                            <input type="text" name="nameManager" class="form-control" maxlength=15 id="nameManager" value="" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="nameManager-help" class="help-block fpbx-help-block"><?php echo _("Name of the manager without spaces and special characters except _ and -. Limit upto 15 characters only.") ?></span>
                                    </div>
                                </div>
                            </div>
                            <!--Manager name END-->
                            <!--Manager secret INI-->
                            <div class="element-container" style="margin-top: 1rem;">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="secretManager"><?php echo _("Secret") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="secretManager"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <input type="password" class="form-control password-meter" id="secretManager" name="secretManager" value="">
                                                <span class="input-group-addon toggle-password" id="pwtoggle" data-id="secretManager"><i class="fa fa-eye"></i></a></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="secretManager-help" class="help-block fpbx-help-block"><?php echo _("Password for the manager.")?></span>
                                    </div>
                                </div>
                            </div>
                            <!--Manager secret END-->
                            <!--Deny INI-->
                            <div class="element-container" style="margin-top: 1rem;">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="denyManager"><?php echo _("Deny") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="denyManager"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="denyManager" name="denyManager" maxlength="1024" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="denyManager-help" class="help-block fpbx-help-block"><?php echo _("If you want to deny many hosts or networks, use & char as separator.<br/><br/>Example: 192.168.1.0/255.255.255.0&10.0.0.0/255.0.0.0")?></span>
                                    </div>
                                </div>
                            </div>
                            <!--Deny END-->
                            <!--Permit INI-->
                            <div class="element-container" style="margin-top: 1rem;">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="permitManager"><?php echo _("Permit") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="permitManager"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="permitManager" name="permitManager" maxlength="1024" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="permitManager-help" class="help-block fpbx-help-block"><?php echo _("If you want to permit many hosts or networks, use & char as separator. Look at deny example.")?></span>
                                    </div>
                                </div>
                            </div>
                            <!--Permit END-->
                            <!--Write Timeout INI-->
                            <div class="element-container" style="margin-top: 1rem;">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="writetimeoutManager"><?php echo _("Write Timeout") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="writetimeoutManager"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="writetimeoutManager" name="writetimeoutManager" value="" required>
                                                <span class="input-group-addon"><?php echo _("milliseconds") ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="writetimeoutManager-help" class="help-block fpbx-help-block"><?php echo _("Sets the timeout used by Asterisk when writing data to the AMI connection for this user")?></span>
                                    </div>
                                </div>
                            </div>
                            <!--Write Timeout END-->
                        </div>
                        <!-- Tab General -->
                        <!-- Tab Perm -->
                        <div id="managerperm" class="tab-pane">
                        

                            <div class="well well-info">
                                <?php echo _("For information on individual permissions please see the Asterisk Manager Documentation")?>
                            </div>

                            <table
                                id="managerpermlist"
                                data-cache="false"
                                data-toggle="table"
                                class="table table-striped">
                                <thead>
                                    <th data-field="name" class="col-md-8">
                                        <?php echo ("Permission")?>
                                    </th>
                                    <th data-field="read" class="col-md-2 text-center">
                                        <?php echo ("Read")?>
                                    </th>
                                    <th data-field="write" class="col-md-2 text-center">
                                        <?php echo ("Write")?>
                                    </th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($permtypes as $type => $title)
                                    {
                                        $rtype = 'r'.$type;
                                        $wtype = 'w'.$type;
                                        echo '<tr>';
                                        echo '  <td class="col-md-8">';
                                        echo $title;
                                        echo '  </td>';
                                        echo '  <td class="col-md-2 text-center">';
                                        echo '      <div class="col-md-12 radioset">';
                                        echo '          <input type="radio" name="'.$rtype.'" id="'.$rtype.'yes" value="1">';
                                        echo '          <label for="'.$rtype.'yes">'._("Yes").'</label>';
                                        echo '          <input type="radio" name="'.$rtype.'" id="'.$rtype.'no">';
                                        echo '          <label for="'.$rtype.'no">'. _("No").'</label>';
                                        echo '      </div>';
                                        echo '  </td>';
                                        echo '  <td class="col-md-2 text-center">';
                                        echo '      <div class="col-md-12 radioset">';
                                        echo '          <input type="radio" name="'.$wtype.'" id="'.$wtype.'yes" value="1">';
                                        echo '          <label for="'.$wtype.'yes">'._("Yes").'</label>';
                                        echo '          <input type="radio" name="'.$wtype.'" id="'.$wtype.'no">';
                                        echo '          <label for="'.$wtype.'no">'. _("No").'</label>';
                                        echo '      </div>';
                                        echo '  </td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="col-md-8">
                                            <b><?php echo _("Toggle All")?> </b>
                                        </td>
                                        <td class="col-md-2 text-center">
                                            <div class="col-md-12 radioset">
                                                <input type="radio" name="rall" id="rallyes" value="1">
                                                <label for="rallyes"><?php echo _("Yes")?></label>
                                                <input type="radio" name="rall" id="rallno" value="off">
                                                <label for="rallno"><?php echo _("No")?></label>
                                            </div>
                                        </td>
                                        <td class="col-md-2 text-center">
                                            <div class="col-md-12 radioset">
                                                <input type="radio" name="wall" id="wallyes" value="1">
                                                <label for="wallyes"><?php echo _("Yes")?></label>
                                                <input type="radio" name="wall" id="wallno" value="0">
                                                <label for="wallno"><?php echo _("No")?></label>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                

                        </div>
                        <!-- Tab Perm -->
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close") ?></button>
                <button type="button" class="btn btn-success" id="submitForm"><?php echo _("Save Changes") ?></button>
            </div>
        </div>
    </div>
</div>