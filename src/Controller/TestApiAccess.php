<?php

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Controller;

use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class TestApiAccess
{
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route(path: '/api/_action/postdirekt/addressfactory/test-api-access', name: 'api.action.postdirekt.addressfactory.test-api-access', methods: ['POST'])]
    public function execute(Request $request): Response
    {
        $username = (string) $request->getPayload()->getString('username');
        $password = (string) $request->getPayload()->get('password');
        $configurationName = (string) $request->getPayload()->get('configurationName');
        $clientId = (string) $request->getPayload()->get('clientId');
        try {
            $service = $this->serviceFactory->createAddressVerificationService(
                $username,
                $password,
                $this->logger
            );

            $session = $service->openSession($configurationName, $clientId);
            $service->closeSession($session);

            return new Response();
        } catch (\Exception $e) {
            $this->logger->log('error', 'PostDirekt Addressfactory: API access test failed', ['exception' => $e, 'username' => $username, 'configurationName' => $configurationName, 'clientId' => $clientId]);
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
