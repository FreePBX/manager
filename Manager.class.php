<?php
namespace FreePBX\modules;
use BMO;
use PDO;
use FreePBX_Helpers;

class Manager extends FreePBX_Helpers implements BMO
{
	const CONF_FILE_NAME_MAIN  = 'manager.conf';
	const CONF_FILE_NAME_EXTRA = 'manager_additional.conf';

	const ERR_PARAM_MISSING = 100;	// The required parameter has not been defined.
	const ERR_EXISTS 		= 110;	// We try to create something that already exists.
	const ERR_NOT_EXISTS 	= 120; 	// We try to obtain data that does not exist.
	const ERR_USER_CONFLICT = 130;	// User conflicts with something.

	protected $tables = array(
		'manager' => 'manager',
	);

	public function getDefault($option = "")
	{
		$default = array(
			'name' 			=> '',
			'secret' 		=> '',
			'deny' 			=> '0.0.0.0/0.0.0.0',
			'permit' 		=> '127.0.0.1/255.255.255.0',
			'read' 			=> 'all',
			'write'			=> 'all',
			'writetimeout' 	=> '100',
		);
		$data_return = $default;
		if (! empty($option))
		{
			$data_return = isset($default[$option]) ? $default[$option] : '';
		}
		return $data_return;
	}

	public function getPermissions($onlykey = false)
	{
		$data_return = array(
			'system'	=> _("System"),
			'call'		=> _("Call"),
			'log' 		=> _("Log"),
			'verbose' 	=> _("Verbose"),
			'command' 	=> _("Command"),
			'agent' 	=> _("Agent"),
			'user' 		=> _("User"),
			// Added for 1.6+
			'config' 	=> _("Config"),
			'dtmf' 		=> _("DTMF"),
			'reporting'	=> _("Reporting"),
			'cdr' 		=> _("CDR"),
			'dialplan' 	=> _("Dialplan"),
			'originate' => _("Originate"),
		);
		if ($onlykey)
		{
			$data_return = array_keys($data_return);
		}

		return $data_return;
	}

	public function genPassword($openssl = true, $bytes = null)
	{
		if ($openssl)
		{
			if (! is_numeric($bytes) || $bytes < 1)
			{
				$bytes = 16;
			}
			$password = openssl_random_pseudo_bytes($bytes);
		}
		if (empty($password))
		{
			$password = uniqid("", true);
		}
		return sha1($password);
	}

	public function install() {}

	public function uninstall() {}

	public function doConfigPageInit($page) { }
	
	public function ajaxRequest($command, &$setting)
	{
		// ** Allow remote consultation with Postman **
		// ********************************************
		// $setting['authenticate'] = false;
		// $setting['allowremote'] = true;
		// return true;
		// ********************************************
		switch($command)
		{
			case "list":
			case 'get':
			case 'update':
			case "delete":
				return true;
			break;
		}
		return false;
	}

	public function ajaxHandler()
	{
		$command = $this->getReq("command", "");
		$data_return = false;

		switch ($command)
		{
			case 'list':
				$data_return = $this->list_managers(true);

				// remove secret from list and convert string to array
				foreach ($data_return as &$item)
				{
    				unset($item['secret']);
					$item['read']  = explode(",", $item['read']);
					$item['write'] = explode(",", $item['write']);
				}
				break;

			case 'get':
				$id = $this->getReq("id", '');
				try
				{
					$qType = ($id == "-1") ? 'new' : 'edit';
					$data  = ($id == "-1") ? $this->getDefault() : $this->get_manager($id);
				}
				catch (\Exception $e)
				{
					$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
				}
				if (empty($data_return))
				{
					$permissions = $this->getPermissions();

					$data['read']  = $data['read'] == 'all'  ? array_keys($permissions) : explode(",", $data['read']);
					$data['write'] = $data['write'] == 'all' ? array_keys($permissions) : explode(",", $data['write']);

					if ($qType == 'new')
					{
						$data['secret'] = $this->genPassword();
					}

					$data_return = array(
						"status" 	  	 => true,
						"data" 		  	 => $data,
						"permissions" 	 => $permissions,
						'urlpermissions' => sprintf("ajax.php?module=manager&command=list_manager_permissions&id=%s", $id),
					);

				}
				break;

			case 'update':
				// Array
				// (
				//     [module] => manager
				//     [command] => update
				//     [type] => edit
				//     [id] => 10
				//     [formdata] => Array
				//         (
				//             [nameManager] => cxpanel
				//             [secretManager] => 123456
				//             [denyManager] => 0.0.0.0/0.0.0.0
				//             [permitManager] => 127.0.0.1/255.255.255.0
				//             [writetimeoutManager] => 100
				//             [rsystem] => 1
				//             [wsystem] => 1
				//             [rcall] => 1
				//             [wcall] => 1
				//             [rlog] => 1
				//             [wlog] => 1
				//             [wcdr] => 1
				//             [rdialplan] => 1
				//             [wdialplan] => 1
				//             [roriginate] => 1
				//             [woriginate] => 1
				//             [rall] => 1
				//             [wall] => 1
				//         )
				// )

				$id    = $this->getReq("id", '');
				$utype = $this->getReq("type", '');
				$form  = $this->getReq("formdata", array());

				if (empty($form))
				{
					$data_return = array("status" => false, "message" => _("No data received!"));
				}
				else
				{
					$name 		  = $form['nameManager'];
					$secret 	  = $form['secretManager'];
					$deny 		  = str_replace("&amp;","&",$form['denyManager']);
					$permit		  = str_replace("&amp;","&",$form['permitManager']);
					$writetimeout = $form['writetimeoutManager'];
					$rights 	  = $this->format_in($form);

					switch ($utype)
					{
						case 'new':
							try
							{
								$this->add_manager($name, $secret, $deny, $permit, $rights['read'], $rights['write'], $writetimeout);
								needreload();
								$data_return = array("status" => true, "message" => _("Create Successfully"), "needreload" => true);
							}
							catch (\Exception $e)
							{
								$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
							}
							break;

						case 'edit':	//just delete and re-add
							try
							{
								if ($this->del_manager($id))
								{
									$this->add_manager($name, $secret, $deny, $permit, $rights['read'], $rights['write'], $writetimeout);
									needreload();
									$data_return = array("status" => true, "message" => _("Update Successfully"), "needreload" => true);
								}
								else
								{
									$data_return = array("status" => false, "message" => _("Update is Failed!"),);
								}
							}
							catch (\Exception $e)
							{
								$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
							}
							break;
						default:
							$data_return = array("status" => false, "message" => _("Type not found!"));
					}
				}
				break;

			case 'delete':
				$id = $this->getReq("id", '');
				try
				{
					if ($this->del_manager($id))
					{
						needreload();
						$data_return = array("status" => true, "message" => _("Removed Successfully"), "needreload" => true);
					}
					else
					{
						$data_return = array("status" => false, "message" => _("Removed Failed!"));
					}
				}
				catch (\Exception $e)
				{
					$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
				}
				break;

			default:
				$data_return = array("status" => false, "message" => _("Command not found!"), "command" => $command);
				break;
		}
		return $data_return;
	}

	
	/**
	 * This returns html to the main page
	 *
	 * @return string html
	 */
	public function showPage($page, $params = array())
	{
		$request = $_REQUEST;
		$data = array(
			"manager" => $this,
			'request' => $request,
			'page' 	  => $page,
		);
		$data = array_merge($data, $params);

		switch ($page)
		{
			case 'main':
				$data_return = load_view(__DIR__ . '/views/page.main.php', $data);
				break;

			case 'grid':
				$data_return  = load_view(__DIR__ . '/views/view.grid.php', $data);
				$data_return .= load_view(__DIR__ . '/views/view.grid.form.php', $data);
				break;

			default:
				$data_return = sprintf(_("Page Not Found (%s)!!!!"), $page);
		}
		return $data_return;
	}

	/**
	 * Get the manager list
	 * @param boolean $all 		True show all data, false show only name and secret.
	 * @return array
	 */
	public function list_managers($all = false)
	{
		if ($all)
		{
			$sql = sprintf('SELECT * FROM %s ORDER BY name', $this->tables['manager']);
		}
		else
		{
			$sql = sprintf("SELECT name, secret FROM %s ORDER BY name", $this->tables['manager']);
		}
		$sth = $this->Database->prepare($sql);
		$sth->execute();
		$results = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if (! is_array($results ))
		{
			$results = array();
		}
		return $results;
	}

	/**
	 * Add a manager
	 */
	public function add_manager($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write, $p_writetimeout=100)
	{
		if (trim($p_name) == "")
		{
			throw new \Exception(_('No name specified!'), self::ERR_PARAM_MISSING);
		}
		if ($this->isExist_manager($p_name, true))
		{
			throw new \Exception(_("This manager already exists!"), self::ERR_EXISTS);
		}
		if ($this->isConflictUser($p_name, true))
		{
			throw new \Exception(_("Can't create, we are conflicting with FreePBX Asterisk Admin User!"), self::ERR_USER_CONFLICT);
		}

		$sql = sprintf("INSERT into `%s` (`name`, `secret`, `deny`, `permit`, `read`, `write`, `writetimeout`) VALUES (?, ?, ?, ?, ?, ?, ?)", $this->tables['manager']);
		$sth = $this->Database->prepare($sql);
		$sth->execute(array($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write, $p_writetimeout));
		return true;
	}
	
	/**
	 * Get manager infos
	 */
	public function get_manager($value, $getByName = false)
	{
		if (trim($value) == "")
		{
			throw new \Exception(_('No manager has been selected!'), self::ERR_PARAM_MISSING);
		}

		$sql = sprintf("SELECT * FROM %s WHERE `%s` = ?", $this->tables['manager'], $getByName ? 'name' : 'manager_id');
		$sth = $this->Database->prepare($sql);
		$sth->execute(array($value));
		$results = $sth->fetch(\PDO::FETCH_ASSOC);
		if (empty($results))
		{
			throw new \Exception(_('The manager does not exist!'), self::ERR_NOT_EXISTS);
		}
		return $results;
	}

	public function isExist_manager($value, $checkByName = false)
	{
		if (trim($value) != "")
		{
			$sql = sprintf("SELECT COUNT(*) FROM %s WHERE `%s` = ?", $this->tables['manager'], $checkByName ? 'name' : 'manager_id');
			$stmt = $this->Database->prepare($sql);
			$stmt->execute(array($value));
			if ($stmt->fetchColumn() > 0)
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * Delete a manager
	 */
	public function del_manager($value, $deleteByName = false)
	{
		if (trim($value) == "")
		{
			throw new \Exception(_('No manager has been selected to be removed!'), self::ERR_PARAM_MISSING);
		}
		if (! $this->isExist_manager($value, $deleteByName))
		{
			throw new \Exception(_('Cannot be deleted as it does not exist!'), self::ERR_NOT_EXISTS);
		}
		if ($this->isConflictUser($value, $deleteByName))
		{
			throw new \Exception(_("Can't delete, we are conflicting with FreePBX Asterisk Admin User!"), self::ERR_USER_CONFLICT);
		}
		
		$sql = sprintf("DELETE FROM %s WHERE `%s` = ?", $this->tables['manager'], $deleteByName ? 'name' : 'manager_id');
		$sth = $this->Database->prepare($sql);
		$sth->execute(array($value));
		return true;
	}


	public function isConflictUser($value, $detectByName = false)
	{
		// if (! $this->isExist_manager($value, $detectByName))
		// {
		// 	throw new \Exception(_('The selected manager does not exist!'), self::ERR_NOT_EXISTS);
		// }
		$ampuser = $this->getAMPMGRUSER();
		$name 	 = $value;
		if (! $detectByName)
		{
			$manager = $this->get_manager($value, false);
			$name    = $manager['name'];
		}
		if($ampuser == $name)
		{
			return true;
		}
		return false;
	}

	/**
	 * Used to set the correct values for the html checkboxes
	 */
	public function format_out($tab)
	{
		$res = array(
			'name'			=> $tab['name'],
			'secret'		=> $tab['secret'],
			'deny'			=> $tab['deny'],
			'permit'		=> $tab['permit'],
			'writetimeout' 	=> $tab['writetimeout'],
		);
		foreach(explode(',', $tab['read']) as $item)
		{
			$res['r'.$item] = true;
		}
		foreach(explode(',', $tab['write']) as $item)
		{
			$res['w'.$item] = true;
		}
		return $res;
	}

	public function format_in($tab)
	{
		$res = array(
			'read'  => array(),
			'write' => array(),
		);

		foreach($this->getPermissions(true) as $value)
		{
			$oRead  = sprintf("r%s", $value);
			$oWrite = sprintf("w%s", $value);

			if (isset($tab[$oRead]) && $tab[$oRead] == 1)
			{
				$res['read'][] = $value;
			}
			if (isset($tab[$oWrite]) && $tab[$oWrite] == 1)
			{
				$res['write'][] = $value;
			}
		}

		$res['read']  = implode(",", $res['read']);
		$res['write'] = implode(",", $res['write']);
		return $res;
	}
	
	public function getAMPMGRUSER()
	{
		return $this->FreePBX->Config->get('AMPMGRUSER');
	}

	/**
	 * Generate configuration file from "processNewHooks", processOldHooks was used before.
	 */
	public function genConfig()
	{
		$config = array();
		foreach ($this->list_managers(true) as $manager)
		{
			$section = array();
			foreach ($this->get_manager($manager['manager_id']) as $key => $value)
			{
				switch ($key)
				{
					case 'secret':
					case 'read':
					case 'write':
						$section[$key] = $value;
					break;

					case 'writetimeout':
						$section[$key] = !empty($value) ? $value : $this->getDefault('writetimeout');;
					break;

					case 'permit':
					case 'deny':
						foreach (explode("&", $value) as $addr)
						{
							if (empty($addr)) { continue; }
							$section[] = sprintf("%s=%s", $key , $addr);
						}
					break;
				}
			}
			$adsettings = $this->getConfig('additional_settings', $manager['name']);
			if(!empty($adsettings)) {
				$section[] = $adsettings;
			}
			$config[$manager['name']] = $section;
		}

		$config[self::CONF_FILE_NAME_EXTRA] = $config;
		return $config;
	}

	public function writeConfig($config)
	{
		$this->FreePBX->WriteConfig($config);
	}

	public function readConfig($option = "") 
	{
		$ASTETCDIR   = $this->FreePBX->Config->get('ASTETCDIR');
		$data_return = @parse_ini_file(sprintf("%s/%s", $ASTETCDIR, self::CONF_FILE_NAME_MAIN), false);
		if (!empty($option))
		{
			$data_return = isset($data_return[$option]) ? $data_return[$option] : '';
		}
		return $data_return;
	}

}
