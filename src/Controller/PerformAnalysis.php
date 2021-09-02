<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use PostDirekt\Addressfactory\Service\ModuleConfig;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use PostDirekt\Addressfactory\Service\OrderUpdater;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class PerformAnalysis
{
    private EntityRepositoryInterface $orderRepository;

    private OrderAnalysis $orderAnalysis;

    private OrderUpdater $orderUpdater;

    private AnalysisStatusUpdater $analysisStatus;

    private ModuleConfig $config;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        OrderAnalysis $orderAnalysis,
        OrderUpdater $orderUpdater,
        AnalysisStatusUpdater $analysisStatus,
        ModuleConfig $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderAnalysis = $orderAnalysis;
        $this->orderUpdater = $orderUpdater;
        $this->analysisStatus = $analysisStatus;
        $this->config = $config;
    }

    /**
     * @Route("/api/postdirekt/addressfactory/perform-analysis",
     *     name="api.action.postdirekt.addressfactory.perform-analysis",
     *     methods={"POST"})
     */
    public function execute(Request $request, Context $context): JsonResponse
    {
        $orderId = $request->get('order_id');
        $order = $this->loadOrder($orderId, $context);

        if ($order === null) {
            return new JsonResponse(
                [
                    'analysisStatus' => '',
                    'analysisResult' => null,
                    'order' => null,
                    'message' => 'postdirekt-addressfactory.performAnalysis.genericError',
                ]
            );
        }

        if ($this->getCountryIso($order) !== 'DE') {
            return new JsonResponse(
                [
                    'analysisStatus' => '',
                    'analysisResult' => null,
                    'order' => null,
                    'message' => 'postdirekt-addressfactory.performAnalysis.countryError',
                ]
            );
        }

        $analysisResult = $this->orderAnalysis->analyse([$order], $context)[$orderId] ?? null;

        $responseData = [
            'analysisResult' => $analysisResult,
            'message' => '',
            'orderNumber' => $order->getOrderNumber(),
            'order' => $order,
            'analysisStatus' => '',
        ];

        if (!$analysisResult) {
            $responseData['message'] = 'postdirekt-addressfactory.performAnalysis.error';

            return new JsonResponse($responseData);
        }

        if ($this->config->isAutoCancelNonDeliverableOrders($order->getSalesChannelId())) {
            $isCanceled = $this->orderUpdater->cancelIfUndeliverable(
                $order,
                $analysisResult,
                $context
            );
            if ($isCanceled) {
                $canceledOrder = $this->loadOrder($orderId, $context);
                $responseData['message'] = 'postdirekt-addressfactory.performAnalysis.cancelled';
                $responseData['order'] = $canceledOrder;

                return new JsonResponse($responseData);
            }
        }

        if ($this->config->isAutoUpdateShippingAddress($order->getSalesChannelId())) {
            $isUpdated = $this->orderAnalysis->updateShippingAddress(
                $orderId,
                $analysisResult,
                $context
            );
            if ($isUpdated) {
                $updatedOrder = $this->loadOrder($orderId, $context);
                $responseData['message'] = 'postdirekt-addressfactory.performAnalysis.updateSuccess';
                $responseData['order'] = $updatedOrder;
                $responseData['analysisStatus'] = $this->analysisStatus->getStatus($orderId, $context);

                return new JsonResponse($responseData);
            }
        }

        return new JsonResponse($responseData);
    }

    private function loadOrder(string $orderId, Context $context): ?OrderEntity
    {
        return $this->orderRepository->search(
            (new Criteria([$orderId]))->addAssociations(['deliveries', 'deliveries.shippingOrderAddress.country']),
            $context
        )->first();
    }

    private function getCountryIso(OrderEntity $order): ?string
    {
        $deliveries = $order->getDeliveries();
        if ($deliveries) {
            $address = $deliveries->getShippingAddress()->first();
            if ($address) {
                $country = $address->getCountry();
                if ($country) {
                    return $country->getIso();
                }
            }
        }

        return null;
    }
}
