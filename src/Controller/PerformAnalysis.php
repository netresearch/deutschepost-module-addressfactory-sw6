<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Addressfactory\Service\ModuleConfig;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use PostDirekt\Addressfactory\Service\OrderUpdater;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class PerformAnalysis extends AbstractController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderAnalysis
     */
    private $orderAnalysis;

    /**
     * @var OrderUpdater
     */
    private $orderUpdater;

    /**
     * @var ModuleConfig
     */
    private $config;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        OrderAnalysis $orderAnalysis,
        OrderUpdater $orderUpdater,
        ModuleConfig $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderAnalysis = $orderAnalysis;
        $this->orderUpdater = $orderUpdater;
        $this->config = $config;
    }

    /**
     * @Route("/api/v{version}/postdirekt/addressfactory/perform-analysis",
     *     name="api.action.postdirekt.addressfactory.perform-analysis",
     *     methods={"POST"})
     */
    public function execute(Request $request, Context $context): JsonResponse
    {
        $orderId = $request->get('order_id');
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(
            (new Criteria([$orderId]))->addAssociation('deliveries'),
            $context
        )->first();

        $analysisResult = $this->orderAnalysis->analyse([$order], $context)[$orderId];

        if (!$analysisResult) {
            return new JsonResponse(
                [
                    'analysisResult' => null,
                    'message' => 'postdirekt-addressfactory.performAnalysis.error',
                    'orderNumber' => $order->getOrderNumber(),
                ]
            );
        }

        if ($this->config->isAutoCancelNonDeliverableOrders($order->getSalesChannelId())) {
            $isCanceled = $this->orderUpdater->cancelIfUndeliverable(
                $order,
                $analysisResult,
                $context
            );
            if ($isCanceled) {
                return new JsonResponse(
                    [
                        'analysisResult' => $analysisResult,
                        'message' => 'postdirekt-addressfactory.performAnalysis.cancelled',
                        'orderNumber' => $order->getOrderNumber(),
                    ]
                );
            }
        }
        if ($this->config->isAutoUpdateShippingAddress($order->getSalesChannelId())) {
            $isUpdated = $this->orderAnalysis->updateShippingAddress(
                $orderId,
                $analysisResult,
                $context
            );
            if ($isUpdated) {
                return new JsonResponse(
                    [
                        'analysisResult' => $analysisResult,
                        'message' => 'postdirekt-addressfactory.performAnalysis.updateSuccess',
                        'orderNumber' => $order->getOrderNumber(),
                    ]
                );
            }
        }

        return new JsonResponse([
            'analysisResult' => $analysisResult,
            'message' => '',
            'orderNumber' => $order->getOrderNumber(),
        ]);
    }
}
