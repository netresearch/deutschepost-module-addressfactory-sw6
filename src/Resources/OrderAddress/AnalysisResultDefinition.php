<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
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

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                /*
                 * @TODO We cannot use FkField here because of an issue that breaks API access to the entity.
                 * @see https://github.com/shopware/platform/pull/714
                 */
                // (new FkField('order_address_id', 'orderAddressId', OrderAddressDefinition::class))
                (new IdField('order_address_id', 'orderAddressId'))
                    ->addFlags(new PrimaryKey(), new Required()),
                (new StringField('status_codes', 'statusCodes'))->addFlags(new Required()),
                (new StringField('first_name', 'firstName'))->addFlags(new Required()),
                (new StringField('last_name', 'lastName'))->addFlags(new Required()),
                (new StringField('city', 'city'))->addFlags(new Required()),
                (new StringField('postal_code', 'postalCode'))->addFlags(new Required()),
                (new StringField('street', 'street'))->addFlags(new Required()),
                new StringField('street_number', 'streetNumber'),
            ]
        );
    }
}
