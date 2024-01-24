<?php
namespace FreePBX\modules\Manager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase
{
	public function runRestore ()
	{
		$configs = $this->getConfigs();
		if(isset($configs['tables'])) {
			$this->importTables($configs['tables']);
		}
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables)
	{
		$this->restoreLegacyDatabase($pdo);
	}
}
