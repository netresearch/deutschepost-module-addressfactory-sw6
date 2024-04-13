<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultCollection;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class UpdateAddress
{
    /**
     * @param EntityRepository<AnalysisResultCollection> $analysisResultRepo
     * @param EntityRepository<OrderAddressCollection> $orderAddressRepo
     * @param EntityRepository<OrderCollection> $orderRepository
     * @param OrderAnalysis $orderAnalysis
     */
    public function __construct(
        private readonly EntityRepository $analysisResultRepo,
        private readonly EntityRepository $orderAddressRepo,
        private readonly EntityRepository $orderRepository,
        private readonly OrderAnalysis $orderAnalysis
    ) {
    }

    #[Route(path: '/api/postdirekt/addressfactory/update-address', name: 'api.action.postdirekt.addressfactory.update-address', methods: ['POST'])]
    public function execute(Request $request, Context $context): JsonResponse
    {
        $orderId = (string) $request->request->get('order_id');

        try {
            $shippingAddressId = $this->getShippingAddressId($orderId, $context);

            $analysisResult = $this->analysisResultRepo->search(
                (new Criteria())->addFilter(new EqualsFilter('orderAddressId', $shippingAddressId)),
                $context
            )->first();
            if (!$analysisResult instanceof AnalysisResultInterface) {
                throw new \RuntimeException('postdirekt-addressfactory.updateAddress.notAnalysed');
            }

            $success = $this->orderAnalysis->updateShippingAddress($orderId, $analysisResult, $context);

            if (!$success) {
                throw new \RuntimeException('postdirekt-addressfactory.updateAddress.error');
            }
        } catch (\RuntimeException $exception) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'orderAddress' => null,
                ]
            );
        }

        $newOrderAddress = $this->orderAddressRepo->search(
            new Criteria([$shippingAddressId]),
            $context
        )->first();

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'postdirekt-addressfactory.updateAddress.success',
                'orderAddress' => $newOrderAddress,
            ]
        );
    }

    private function getShippingAddressId(string $orderId, Context $context): string
    {
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(
            (new Criteria([$orderId]))->addAssociation('deliveries.shippingOrderAddress'),
            $context
        )->first();

        $deliveries = $order->getDeliveries();
        if (!$deliveries) {
            throw new \RuntimeException('postdirekt-addressfactory.updateAddress.noDeliveries');
        }
        $shippingAddress = $deliveries->getShippingAddress()->first();
        if (!$shippingAddress) {
            throw new \RuntimeException('postdirekt-addressfactory.updateAddress.noDeliveryAddress');
        }

        return $shippingAddress->getId();
    }
}
