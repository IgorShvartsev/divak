<?php
namespace Migrate\Models;

use \Db\PdoModel;

class MigrateModel extends PdoModel
{
    /**
     * @var string
     */
    public $table = 'migrations';


    public function __construct($pdo = null)
    {
        parent::__construct($pdo);
        $this->createIfNotExixtsMigrationTable();
    }
    /**
     * Check if migration name already exists in migration table
     *
     * @param string $migrationName
     * @return bool
     */
    public function checkMigrationHit($migrationName)
    {
        $result = $this->query(
            ' SELECT `id` FROM `' . $this->table . '` '
            . " WHERE `name` = ? "
        )->fetch([$migrationName]);

        return !empty($result);
    }

    /**
     * Save migration name into migration table
     *
     * @param string $migrationName
     */
    public function saveMigration($migrationName)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->query(
            ' INSERT INTO `' . $this->table . '` (`name`, `date`) '
            . " VALUES (?, '" . $now->format('Y-m-d H:i:s') . "');"
        )->execute([$migrationName]);
    }

    /**
     * Execute query
     * 
     * @param string $query
     */
    public function executeQuery($query)
    {
        $this->query($query)->execute();
    }

    /**
     * Create  migration table if it still does not exist
     *
     */
    protected function createIfNotExixtsMigrationTable()
    {
        $this->query(
            'CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `date` DATETIME,
                PRIMARY KEY (`id`),
                INDEX (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        )->execute();
    }
}
