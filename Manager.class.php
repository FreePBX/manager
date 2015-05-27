<?php
namespace FreePBX\modules;

class Manager implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}
    public function install() {}
    public function uninstall() {}
    public function backup() {}
    public function restore($backup) {}
    public function doConfigPageInit($page) {
      $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
      //the extension we are currently displaying
      $managerdisplay = isset($_REQUEST['managerdisplay'])?$_REQUEST['managerdisplay']:'';
      $name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
      $secret = isset($_REQUEST['secret'])?$_REQUEST['secret']:'';
      $deny = isset($_REQUEST['deny'])?$_REQUEST['deny']:'0.0.0.0/0.0.0.0';
      $permit = isset($_REQUEST['permit'])?$_REQUEST['permit']:'127.0.0.1/255.255.255.0';
      $engineinfo = engine_getinfo();
      $astver =  $engineinfo['version'];
      //if submitting form, update database
      global $amp_conf;
      if($action == 'add' || $action == 'delete') {
      	$ampuser = $amp_conf['AMPMGRUSER'];
      	if($ampuser == $name) {
      		$action = 'conflict';
      	}
      }
      switch ($action) {
      	case "add":
      		$rights = manager_format_in($_REQUEST);
      		manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
          $_REQUEST['managerdisplay'] = $name;
      		needreload();
      	break;
      	case "delete":
      		manager_del($managerdisplay);
      		needreload();
      	break;
      	case "edit":  //just delete and re-add
      		manager_del($name);
      		$rights = manager_format_in($_REQUEST);
      		manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
      		needreload();
      	break;
      	case "conflict":
      		//do nothing we are conflicting with the FreePBX Asterisk Manager User
      	break;
      }
    }
	public function getActionBar($request) {
		$buttons = array();
		switch($request['display']) {
			case 'manager':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['managerdisplay'])) {
					unset($buttons['delete']);
				}
        if(!isset($_REQUEST['view'])){
          $buttons = array();
        }
			break;
		}
		return $buttons;
	}
  public function ajaxRequest($req, &$setting) {
       switch ($req) {
           case 'getJSON':
               return true;
           break;
           default:
               return false;
           break;
       }
   }
   public function ajaxHandler(){
       switch ($_REQUEST['command']) {
           case 'getJSON':
               switch ($_REQUEST['jdata']) {
                   case 'grid':
                       return $this->listManagers();
                   break;

                   default:
                       return false;
                   break;
               }
           break;

           default:
               return false;
           break;
       }
   }
  public function listManagers(){
    $dbh = $this->db;
    $sql = "SELECT manager_id, name, deny, permit FROM manager ORDER BY name";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $res = is_array($ret)?$ret:array();
    return $res;
  }
}
