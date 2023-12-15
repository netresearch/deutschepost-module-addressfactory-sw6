<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service\Analysis;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResult;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use PostDirekt\Sdk\AddressfactoryDirect\Api\Data\RecordInterface;

class ResponseMapper
{
    final public const PARCELSTATION = 'Packstation';
    final public const POSTSTATION = 'Postfiliale';
    final public const POSTFACH = 'Postfach';

    public function __construct(private readonly AddressTypeCodeFilter $addressTypeCodeFilter)
    {
    }

    /**
     * @param RecordInterface[] $records
     * @param string[]          $addressIds
     *
     * @return array<string, AnalysisResultInterface>
     */
    public function mapRecordsResponse(array $records, array $addressIds): array
    {
        $newAnalysisResults = [];
        foreach ($records as $record) {
            $result = $this->mapAddressTypes($record);
            if (($person = $record->getPerson()) !== null) {
                $result->setFirstName($person->getFirstName());
                $result->setLastName($person->getLastName());
            }
            $result->setOrderAddressId($addressIds[$record->getRecordId()]);
            $statusCodes = $this->addressTypeCodeFilter->filterCodes($record);
            $result->setStatusCodes($statusCodes);
            $newAnalysisResults[$result->getOrderAddressId()] = $result;
        }

        return $newAnalysisResults;
    }

    private function mapAddressTypes(RecordInterface $record): AnalysisResult
    {
        $result = new AnalysisResult();
        if (($address = $record->getAddress()) !== null) {
            $result->setPostalCode($address->getPostalCode());
            $result->setCity($address->getCity());
            $result->setStreet($address->getStreetName());
            $result->setStreetNumber(
                trim(
                    implode(
                        ' ',
                        [
                            $address->getStreetNumber(),
                            $address->getStreetNumberAddition(),
                        ]
                    )
                )
            );
        }

        if (($parcelStation = $record->getParcelStation()) !== null) {
            $result->setPostalCode($parcelStation->getPostalCode());
            $result->setCity($parcelStation->getCity());
            $result->setStreet(self::PARCELSTATION);
            $result->setStreetNumber($parcelStation->getNumber());
        }

        if (($postOffice = $record->getPostOffice()) !== null) {
            $result->setPostalCode($postOffice->getPostalCode());
            $result->setCity($postOffice->getCity());
            $result->setStreet(self::POSTSTATION);
            $result->setStreetNumber($postOffice->getNumber());
        }

        if (($postalBox = $record->getPostalBox()) !== null) {
            $result->setPostalCode($postalBox->getPostalCode());
            $result->setCity($postalBox->getCity());
            $result->setStreet(self::POSTFACH);
            $result->setStreetNumber($postalBox->getNumber());
        }

        if (($bulkReceiver = $record->getBulkReceiver()) !== null) {
            $result->setPostalCode($bulkReceiver->getPostalCode());
            $result->setCity($bulkReceiver->getCity());
            $result->setStreet($bulkReceiver->getName());
            $result->setStreetNumber('');
        }

        return $result;
    }
}
