<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class TestApiAccess extends AbstractController
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ServiceFactory $serviceFactory, LoggerInterface $logger)
    {
        $this->serviceFactory = $serviceFactory;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/v{version}/postdirekt/addressfactory/test-api-access",
     *     name="api.action.postdirekt.addressfactory.test-api-access",
     *     methods={"POST"})
     */
    public function execute(Request $request, Context $context): Response
    {
        try {
            $username = $request->get('username');
            $password = $request->get('password');
            $configurationName = $request->get('configurationName');
            $clientId = $request->get('clientId');

            $service = $this->serviceFactory->createAddressVerificationService(
                $username,
                $password,
                $this->logger
            );

            $session = $service->openSession($configurationName, $clientId);
            $service->closeSession($session);

            return Response::create();
        } catch (\Exception $e) {
            return Response::create(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
