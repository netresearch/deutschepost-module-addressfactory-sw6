<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResult;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use PostDirekt\Sdk\AddressfactoryDirect\Api\Data\RecordInterface;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\AuthenticationException;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\ServiceException;
use PostDirekt\Sdk\AddressfactoryDirect\Model\RequestType\InRecordWSType;
use PostDirekt\Sdk\AddressfactoryDirect\RequestBuilder\RequestBuilder;
use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class AddressAnalysis
{
    /**
     * @var EntityRepositoryInterface
     */
    private $analysisResultRepo;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityRepositoryInterface $analysisResultRepo,
        ModuleConfig $moduleConfig,
        ServiceFactory $serviceFactory,
        RequestBuilder $requestBuilder,
        LoggerInterface $logger
    ) {
        $this->analysisResultRepo = $analysisResultRepo;
        $this->moduleConfig = $moduleConfig;
        $this->serviceFactory = $serviceFactory;
        $this->requestBuilder = $requestBuilder;
        $this->logger = $logger;
    }

    /**
     * @param OrderAddressEntity[] $addresses
     *
     * @throws \RuntimeException
     *
     * @return array<string, AnalysisResultInterface>
     */
    public function analyse(array $addresses, Context $context): array
    {
        $addressIds = [];
        foreach ($addresses as $address) {
            $addressIds[] = $address->getId();
        }
        $analysisResults = $this->analysisResultRepo->search(
            (new Criteria())->addFilter(new EqualsAnyFilter('orderAddressId', $addressIds)),
            $context
        )->getElements();

        /** @var InRecordWSType[] $recordRequests */
        $recordRequests = array_reduce(
            $addresses,
            function (array $recordRequests, OrderAddressEntity $orderAddress) use ($analysisResults, $addressIds) {
                if (!array_key_exists($orderAddress->getId(), $analysisResults)) {
                    $recordRequests[] = $this->buildRequest($orderAddress, $addressIds);
                }

                return $recordRequests;
            },
            []
        );

        if (empty($recordRequests)) {
            return $analysisResults;
        }

        try {
            $service = $this->serviceFactory->createAddressVerificationService(
                $this->moduleConfig->getApiUser(),
                $this->moduleConfig->getApiPassword(),
                $this->logger,
                false
            );
            $records = $service->getRecords(
                $recordRequests,
                null,
                $this->moduleConfig->getConfigurationName(),
                $this->moduleConfig->getClientId()
            );
            $newAnalysisResults = $this->mapRecordsResponse($records, $addressIds);
            $dalData = [];
            foreach ($newAnalysisResults as $result) {
                $dalData[] = \json_decode((string) \json_encode($result), true);
            }
            $this->analysisResultRepo->upsert($dalData, $context);
        } catch (AuthenticationException $exception) {
            throw new \RuntimeException('Authentication error: ' . $exception->getMessage());
        } catch (ServiceException $exception) {
            throw new \RuntimeException('Service exception: ' . $exception->getMessage());
        }

        // add new records to previously analysis results from db, do a union on purpose to keep keys
        $analysisResults = $newAnalysisResults + $analysisResults;

        return $analysisResults;
    }

    /**
     * @param string[] $addressIds
     */
    private function buildRequest(OrderAddressEntity $address, array $addressIds): object
    {
        /* The record id is used in mapRecordsResponse to match webservice results to invidual addresses */
        $this->requestBuilder->setMetadata(array_flip($addressIds)[$address->getId()]);
        $this->requestBuilder->setAddress(
            $address->getCountryId(),
            $address->getZipcode(),
            $address->getCity(),
            $address->getStreet(),
            ''
        );
        $this->requestBuilder->setPerson(
            $address->getFirstName(),
            $address->getLastName()
        );

        return $this->requestBuilder->create();
    }

    /**
     * @param RecordInterface[] $records
     * @param string[]          $addressIds
     *
     * @return array<string, AnalysisResultInterface>
     */
    private function mapRecordsResponse(array $records, array $addressIds): array
    {
        $newAnalysisResults = [];
        foreach ($records as $record) {
            $result = new AnalysisResult();
            $result->setOrderAddressId($addressIds[$record->getRecordId()]);
            $result->setStatusCodes($record->getStatusCodes());
            $person = $record->getPerson();
            if ($person !== null) {
                $result->setFirstName($person->getFirstName());
                $result->setLastName($person->getLastName());
            } else {
                $result->setFirstName('');
                $result->setLastName('');
            }
            $address = $record->getAddress();
            if ($address !== null) {
                $result->setPostalCode($address->getPostalCode());
                $result->setCity($address->getCity());
                $result->setStreet($address->getStreetName());
                $result->setStreetNumber(trim(implode(' ', [
                    $address->getStreetNumber(),
                    $address->getStreetNumberAddition(),
                ])));
            }
            $newAnalysisResults[$result->getOrderAddressId()] = $result;
        }

        return $newAnalysisResults;
    }
}
