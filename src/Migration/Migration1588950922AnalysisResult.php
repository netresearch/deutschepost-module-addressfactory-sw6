<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1588950922AnalysisResult extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1588950922;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `postdirekt_addressfactory_analysis_result` (
    `order_address_id` BINARY(16) NOT NULL UNIQUE,
    `status_codes` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `postal_code` VARCHAR(255) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `street_number` VARCHAR(255),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (order_address_id),
    FOREIGN KEY (order_address_id) REFERENCES order_address(id)
)
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
