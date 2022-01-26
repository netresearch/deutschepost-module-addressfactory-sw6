<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AnalysisResultDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'postdirekt_addressfactory_analysis_result';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return AnalysisResultCollection::class;
    }

    public function getEntityClass(): string
    {
        return AnalysisResult::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderAddressDefinition::class;
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

            (new FkField('order_address_id', 'orderAddressId', OrderAddressDefinition::class))->setFlags(new Required()),
            (new ReferenceVersionField(OrderAddressDefinition::class, 'order_address_version_id'))->setFlags(new Required()),

            (new StringField('status_codes', 'statusCodes'))->addFlags(new Required()),
            (new StringField('first_name', 'firstName'))->addFlags(new Required()),
            (new StringField('last_name', 'lastName'))->addFlags(new Required()),
            (new StringField('city', 'city'))->addFlags(new Required()),
            (new StringField('postal_code', 'postalCode'))->addFlags(new Required()),
            (new StringField('street', 'street'))->addFlags(new Required()),
            new StringField('street_number', 'streetNumber'),
            new OneToOneAssociationField('order_address', 'order_address_id', 'id', OrderAddressDefinition::class, false),
        ]);
    }
}
