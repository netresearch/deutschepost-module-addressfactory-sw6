<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(AnalysisStatus $entity)
 * @method void                set(string $key, AnalysisStatus $entity)
 * @method \Generator<mixed, AnalysisStatus, mixed, mixed>          getIterator()
 * @method AnalysisStatus[]    getElements()
 * @method AnalysisStatus|null get(string $key)
 * @method AnalysisStatus|null first()
 * @method AnalysisStatus|null last()
 * @extends EntityCollection<AnalysisStatus>
 */
class AnalysisStatusCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AnalysisStatus::class;
    }
}
