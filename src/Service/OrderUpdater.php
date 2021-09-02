<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\ShopwareHttpException;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

class OrderUpdater
{
    private DeliverabilityCodes $deliverabilityCodes;

    private StateMachineRegistry $stateMachineRegistry;

    public function __construct(DeliverabilityCodes $deliverabilityCodes, StateMachineRegistry $stateMachineRegistry)
    {
        $this->deliverabilityCodes = $deliverabilityCodes;
        $this->stateMachineRegistry = $stateMachineRegistry;
    }

    /**
     * @return bool If OrderEntity was cancelled
     */
    public function cancelIfUndeliverable(
        OrderEntity $order,
        AnalysisResultInterface $analysisResult,
        Context $context
    ): bool {
        $score = $this->deliverabilityCodes->computeScore($analysisResult->getStatusCodes());
        if ($score !== DeliverabilityCodes::UNDELIVERABLE) {
            return false;
        }

        /* @see \Shopware\Core\Checkout\Order\Api\OrderActionController::orderStateTransition */
        try {
            $stateMachineStates = $this->stateMachineRegistry->transition(
                new Transition(
                    'order',
                    $order->getId(),
                    'cancel',
                    'stateId'
                ),
                $context
            );
        } catch (ShopwareHttpException $exception) {
            return false;
        }

        $toPlace = $stateMachineStates->get('toPlace');

        if (!$toPlace || $toPlace->getTechnicalName() !== 'cancelled') {
            return false;
        }

        return true;
    }
}
