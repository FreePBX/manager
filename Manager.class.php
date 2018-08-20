<?php
namespace FreePBX\modules;
use BMO;
use PDO;
use FreePBX_Helpers;

class Manager extends FreePBX_Helpers implements BMO {

	public function install() {}
	public function uninstall() {
	}

	public function doConfigPageInit($page) {
		$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
		//the extension we are currently displaying
		$managerdisplay = isset($_REQUEST['managerdisplay'])?$_REQUEST['managerdisplay']:'';
		$name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
		$secret = isset($_REQUEST['secret'])?$_REQUEST['secret']:'';
		$deny = isset($_REQUEST['deny'])?$_REQUEST['deny']:'0.0.0.0/0.0.0.0';
		$permit = isset($_REQUEST['permit'])?$_REQUEST['permit']:'127.0.0.1/255.255.255.0';
		$engineinfo = engine_getinfo();
		$writetimeout = isset($_REQUEST['writetimeout'])?$_REQUEST['writetimeout']:'100';
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
			manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write'],$writetimeout);
			needreload();
			break;
		case "delete":
			manager_del($managerdisplay);
			needreload();
			break;
		case "edit":  //just delete and re-add
			manager_del($name);
			$rights = manager_format_in($_REQUEST);
			manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write'],$writetimeout);
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
	public function ajaxRequest($command, &$setting) {
        if($command === true){
            return true;
        }
        return false;
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
	public function listManagers($all = false){
        $sql = "SELECT manager_id, name, deny, permit FROM manager ORDER BY name";
        if ($all) {
            $sql = 'SELECT manager_id, name, deny, permit FROM manager ORDER BY name';
        }
        return $this->Database->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function upsert($id, $p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write, $p_writetimeout = 100){
        $sql = 'REPLACE INTO manager (`manager_id`, `name`, `secret`, `deny`, `permit`, `read`, `write`, `writetimeout`) VALUES (:manager_id, :name, :secret, :deny, :permit, :read, :write, :writetimeout)';
        $this->Database->prepare($sql)
         ->execute([
            ':manager_id' => $id, 
            ':name' => $p_name, 
            ':secret' => $p_secret, 
            ':deny' => $p_deny, 
            ':permit' => $p_permit, 
            ':read' => $p_read, 
            ':write' => $p_write, 
            ':writetimeout' => $p_write,
        ]);
        return $this;
	}
	public function setDatabase($pdo){
	$this->Database = $pdo;
	return $this;
	}
	
	public function resetDatabase(){
	$this->Database = $this->FreePBX->Database;
	return $this;
	}
}