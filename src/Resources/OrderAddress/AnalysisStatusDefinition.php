<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AnalysisStatusDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'postdirekt_addressfactory_analysis_status';

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

    protected function getParentDefinitionClass(): ?string
    {
        return OrderDefinition::class;
    }

    /**
     * Declare the analysis result schema.
     *
     * phpcs:disable Generic.Files.LineLength.TooLong
     *
     * It is not possible to make the primary key a foreign key to the
     * order address entity because of an issue that breaks API access.
     *
     * @see https://github.com/shopware/platform/pull/714
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->setFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class, 'order_version_id'))->setFlags(new Required()),

            (new StringField('status', 'status'))->addFlags(new Required()),
            new OneToOneAssociationField('order', 'order_id', 'id', OrderDefinition::class, false),
        ]);
    }
}
