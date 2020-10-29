<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AnalysisStatusDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'postdirekt_addressfactory_analysis_status';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return AnalysisStatusCollection::class;
    }

    public function getEntityClass(): string
    {
        return AnalysisStatus::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                /*
                 * @TODO We cannot use FkField here because of an issue that breaks API access to the entity.
                 * @see https://github.com/shopware/platform/pull/714
                 */
                //(new FkField('order_id', 'orderId', OrderDefinition::class))
                (new IdField('order_id', 'orderId'))
                    ->addFlags(new PrimaryKey(), new Required()),
                (new StringField('status', 'status'))
                    ->addFlags(new Required()),
                (new OneToManyAssociationField('order', OrderDefinition::class, 'id'))->addFlags(
                    new CascadeDelete()
                ),
            ]
        );
    }
}
