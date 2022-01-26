<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class AutoProcess extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'postdirekt_addressfactory.auto_process';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}
