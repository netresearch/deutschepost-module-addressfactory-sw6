<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Extension;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatusDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                'analysisStatus',
                'id',
                'order_id',
                AnalysisStatusDefinition::class,
                true
            ))
        );
    }

    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }
}
