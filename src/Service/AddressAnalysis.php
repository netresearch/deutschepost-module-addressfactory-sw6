<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use PostDirekt\Addressfactory\Service\Analysis\ResponseMapper;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\AuthenticationException;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\ServiceException;
use PostDirekt\Sdk\AddressfactoryDirect\Model\RequestType\InRecordWSType;
use PostDirekt\Sdk\AddressfactoryDirect\RequestBuilder\RequestBuilder;
use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\Country\CountryEntity;

class AddressAnalysis
{
    public function __construct(
        private readonly ResponseMapper $responseMapper,
        private readonly EntityRepository $analysisResultRepo,
        private readonly ModuleConfig $moduleConfig,
        private readonly ServiceFactory $serviceFactory,
        private readonly RequestBuilder $requestBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param OrderAddressEntity[] $addresses
     *
     * @throws \RuntimeException
     * @return array<string, AnalysisResultInterface>
     */
    public function analyse(array $addresses, Context $context): array
    {
        $addressIds = [];
        foreach ($addresses as $address) {
            $addressIds[] = $address->getId();
        }
        /** @var array<string, AnalysisResultInterface> $analysisResults */
        $analysisResults = $this->analysisResultRepo->search(
            (new Criteria())->addFilter(new EqualsAnyFilter('orderAddressId', $addressIds)),
            $context
        )->getElements();

        /** @var InRecordWSType[] $recordRequests */
        $recordRequests = array_reduce(
            $addresses,
            function (array $recordRequests, OrderAddressEntity $orderAddress) use ($analysisResults, $addressIds) {
                if (!\array_key_exists($orderAddress->getId(), $analysisResults)) {
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
            $newAnalysisResults = $this->responseMapper->mapRecordsResponse($records, $addressIds);
            $dalData = [];
            foreach ($newAnalysisResults as $result) {
                $dalData[] = json_decode((string) json_encode($result, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            }
            $this->analysisResultRepo->upsert($dalData, $context);
        } catch (AuthenticationException $exception) {
            throw new \RuntimeException('Authentication error: ' . $exception->getMessage());
        } catch (ServiceException $exception) {
            throw new \RuntimeException('Service exception: ' . $exception->getMessage());
        }

        // add new records to previously analysis results from db, do a union on purpose to keep keys
        return $newAnalysisResults + $analysisResults;
    }

    /**
     * @param string[] $addressIds
     */
    private function buildRequest(OrderAddressEntity $address, array $addressIds): object
    {
        /* The record id is used in mapRecordsResponse to match webservice results to individual addresses */
        $this->requestBuilder->setMetadata(array_flip($addressIds)[$address->getId()]);
        /** @var CountryEntity $countryEntity */
        $countryEntity = $address->getCountry();
        $this->requestBuilder->setAddress(
            $countryEntity->getIso(),
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
}
