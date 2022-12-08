<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(AnalysisResult $entity)
 * @method void                set(string $key, AnalysisResult $entity)
 * @method \Generator<mixed, AnalysisResult, mixed, mixed>          getIterator()
 * @method AnalysisResult[]    getElements()
 * @method AnalysisResult|null get(string $key)
 * @method AnalysisResult|null first()
 * @method AnalysisResult|null last()
 * @extends EntityCollection<AnalysisResult>
 */
class AnalysisResultCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AnalysisResult::class;
    }
}
