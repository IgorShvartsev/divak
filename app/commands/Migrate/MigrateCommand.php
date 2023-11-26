<?php
namespace Migrate;

use Contract\CommandInterface;

class MigrateCommand implements CommandInterface
{
    const MIGRATE_FILE_LIST = __DIR__ . '/MigrateFileList.php';
    const MIGRATE_FILES_DIR = __DIR__ . '/MigrateFiles';
    
    /**
     * @var Models\MigrateModel
     */
    protected $migrateModel;

    /**
     * @var array
     */
    protected $migrateFileList;

    public function __construct(Models\MigrateModel $migrateModel)
    {
        $this->migrateModel = $migrateModel;
        $this->migrateFileList = include self::MIGRATE_FILE_LIST;
    }

    public function execute()
    {
        foreach ($this->migrateFileList as $fileName) {
            $fileName = str_replace('.sql', '', $fileName);
            $file = self::MIGRATE_FILES_DIR . '/' . $fileName . '.sql';
            
            if (file_exists($file)) {
                if (!$this->migrateModel->checkMigrationHit($fileName)) {
                    $query = file_get_contents($file);
                    $query = trim($query);
                    $this->migrateModel->executeQuery($query);
                    $this->migrateModel->saveMigration($fileName);
                    echo 'Migration ' . $fileName . ' executed' . PHP_EOL;
                    flush();
                }
            } else {
                $fileEx = self::MIGRATE_FILES_DIR  . '/' . $fileName . '.php';
                
                if (file_exists($fileEx)) {
                    if (!$this->migrateModel->checkMigrationHit($fileName)) {
                        include $fileEx;
                        $this->migrateModel->saveMigration($fileName);
                        echo 'Migration ' . $fileName . ' executed' .PHP_EOL;
                        flush();
                    }
                } else {
                    echo 'Migration ' . $file . ' doesn\'t exist';
                    flush();
                }
            }
        }
    }
}
