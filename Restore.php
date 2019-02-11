<?php
namespace FreePBX\modules\Manager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
      $configs = $this->getConfigs();
      foreach ($configs as $manager) {
        $this->FreePBX->Manager->upsert((int)$manager['manager_id'], $manager['name'], $manager['secret'], $manager['deny'], $manager['permit'], $manager['read'], $manager['write'], $manager['writetimeout']);
      }
  }

  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
    $tables = array_flip($tables+$unknownTables);
    if(!isset($tables['manager'])){
      return $this;
    }
    $bmo = $this->FreePBX->Manager;
    $bmo->setDatabase($pdo);
    $data = $bmo->listManagers(true);
    $bmo->resetDatabase();
    foreach ($data as $manager) {
      $bmo->upsert((int)$manager['manager_id'], $manager['name'], $manager['secret'], $manager['deny'], $manager['permit'], $manager['read'], $manager['write'], $manager['writetimeout']);
    }
      return $this;
  }
}