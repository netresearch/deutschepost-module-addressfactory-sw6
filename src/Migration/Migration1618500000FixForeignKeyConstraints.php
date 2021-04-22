<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Recreate tables for proper foreign key usage.
 *
 * Not using destructive update as there is no control over
 * the order of execution apart from excluding the plugin from
 * automatic migrations which seems unacceptable in the long run.
 */
class Migration1618500000FixForeignKeyConstraints extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1618500000;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $resultTableSql = <<<SQL
DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_result`;
CREATE TABLE `postdirekt_addressfactory_analysis_result` (
    `id` BINARY(16) NOT NULL,
    `order_address_id` BINARY(16) NOT NULL,
    `order_address_version_id` BINARY(16) NOT NULL,
    `status_codes` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `postal_code` VARCHAR(255) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `street_number` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.analysis_result.order_address_id` FOREIGN KEY (`order_address_id`, `order_address_version_id`)
      REFERENCES `order_address` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $statusTableSql = <<<SQL
DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_status`;
CREATE TABLE `postdirekt_addressfactory_analysis_status` (
    `id` BINARY(16) NOT NULL,
    `order_id` BINARY(16) NOT NULL,
    `order_version_id` BINARY(16) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.analysis_status.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
      REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($resultTableSql);
        $connection->executeStatement($statusTableSql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
