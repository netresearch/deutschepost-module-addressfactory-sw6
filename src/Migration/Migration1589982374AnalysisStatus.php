<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1589982374AnalysisStatus extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589982374;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `postdirekt_addressfactory_analysis_status` (
    `order_id` BINARY(16) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`order_id`),
    FOREIGN KEY (`order_id`) REFERENCES `order` (id)
)
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
