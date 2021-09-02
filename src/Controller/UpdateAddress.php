<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Addressfactory\Service\OrderAnalysis;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class UpdateAddress
{
    private EntityRepositoryInterface $analysisResultRepo;

    private EntityRepositoryInterface $orderAddressRepo;

    private EntityRepositoryInterface $orderRepository;

    private OrderAnalysis $orderAnalysis;

    public function __construct(
        EntityRepositoryInterface $analysisResultRepo,
        EntityRepositoryInterface $orderAddressRepo,
        EntityRepositoryInterface $orderRepository,
        OrderAnalysis $orderAnalysis
    ) {
        $this->analysisResultRepo = $analysisResultRepo;
        $this->orderAddressRepo = $orderAddressRepo;
        $this->orderRepository = $orderRepository;
        $this->orderAnalysis = $orderAnalysis;
    }

    /**
     * @Route("/api/postdirekt/addressfactory/update-address",
     *     name="api.action.postdirekt.addressfactory.update-address",
     *     methods={"POST"})
     */
    public function execute(Request $request, Context $context): JsonResponse
    {
        $orderId = $request->get('order_id');

        try {
            $shippingAddressId = $this->getShippingAddressId($orderId, $context);

            $analysisResult = $this->analysisResultRepo->search(
                (new Criteria())->addFilter(new EqualsFilter('orderAddressId', $shippingAddressId)),
                $context
            )->first();
            if (!$analysisResult) {
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
            (new Criteria([$orderId]))->addAssociation('deliveries'),
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
