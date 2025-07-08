<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Extension;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderAddressExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                'analysisResult',
                'id',
                'order_address_id',
                AnalysisResultDefinition::class,
                false
            ))
        );
    }

    public function getEntityName(): string
    {
        return OrderAddressDefinition::ENTITY_NAME;
    }
}
