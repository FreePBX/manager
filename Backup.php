<?php
namespace FreePBX\modules\Manager;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase
{
    public function runBackup($id,$transaction)
    {
        $configs = [
			'tables'  => $this->dumpTables(),
		];
		$this->addConfigs($configs);
    }
}