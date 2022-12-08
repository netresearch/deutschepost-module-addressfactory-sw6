<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}}
 */
class TestApiAccess
{
    private ServiceFactory $serviceFactory;

    private LoggerInterface $logger;

    public function __construct(ServiceFactory $serviceFactory, LoggerInterface $logger)
    {
        $this->serviceFactory = $serviceFactory;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/postdirekt/addressfactory/test-api-access",
     *     name="api.action.postdirekt.addressfactory.test-api-access",
     *     methods={"POST"})
     */
    public function execute(Request $request): Response
    {
        try {
            $username = (string) $request->request->get('username');
            $password = (string) $request->request->get('password');
            $configurationName = (string) $request->request->get('configurationName');
            $clientId = (string) $request->request->get('clientId');

            $service = $this->serviceFactory->createAddressVerificationService(
                $username,
                $password,
                $this->logger
            );

            $session = $service->openSession($configurationName, $clientId);
            $service->closeSession($session);

            return new Response();
        } catch (\Exception $e) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
