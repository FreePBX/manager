<?php
namespace FreePBX\modules\Manager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
      $configs = $this->getConfigs();
      foreach ($configs as $manager) {
        $this->FreePBX->Manager->upsert($manager['manager_id'], $manager['name'], $manager['secret'], $manager['deny'], $manager['permit'], $manager['read'], $manager['write'], $manager['writetimeout']);
      }
  }
}