<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CartCodeWidget\EventSubscriber;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface;
use SprykerShop\Yves\CartCodeWidget\Plugin\Router\CartCodeAsyncWidgetRouteProviderPlugin;
use SprykerShop\Yves\CartCodeWidget\Plugin\Router\CartCodeWidgetRouteProviderPlugin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class SecurityBlockerCartCodeEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected const SECURITY_BLOCKER_CART_CODE_ENTITY_TYPE = 'cart-code';

    /**
     * @var int
     */
    protected const KERNEL_REQUEST_SUBSCRIBER_PRIORITY = 9;

    /**
     * @uses \SprykerShop\Yves\CartCodeWidget\Controller\AbstractCodeController::PARAM_REDIRECT_ROUTE_NAME
     *
     * @var string
     */
    protected const PARAM_REDIRECT_ROUTE_NAME = 'redirectRouteName';

    /**
     * @var string
     */
    protected const FLASH_MESSAGES_ERROR_KEY = 'flash.messages.error';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TOO_MANY_REQUESTS = 'cart_code_widget.error.too_many_requests';

    public function __construct(
        protected SecurityBlockerClientInterface $securityBlockerClient,
        protected RouterInterface $router,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', static::KERNEL_REQUEST_SUBSCRIBER_PRIORITY],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isCartCodeAddRequest($request)) {
            return;
        }

        $securityCheckAuthContextTransfer = (new SecurityCheckAuthContextTransfer())
            ->setType(static::SECURITY_BLOCKER_CART_CODE_ENTITY_TYPE)
            ->setIp($request->getClientIp());

        $securityCheckAuthResponseTransfer = $this->securityBlockerClient->incrementLoginAttemptCount(
            $securityCheckAuthContextTransfer,
        );

        if (!$securityCheckAuthResponseTransfer->getIsBlocked()) {
            return;
        }

        $this->blockRequest($event);
    }

    protected function isCartCodeAddRequest(Request $request): bool
    {
        $route = $request->attributes->get('_route');

        if ($route === null) {
            return false;
        }

        return in_array($route, $this->getProtectedRoutes(), true)
            && $request->getMethod() === Request::METHOD_POST;
    }

    protected function blockRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add(
            static::FLASH_MESSAGES_ERROR_KEY,
            static::GLOSSARY_KEY_TOO_MANY_REQUESTS,
        );

        $event->setResponse(new RedirectResponse($this->getBlockedRedirectUrl($request)));
    }

    protected function getBlockedRedirectUrl(Request $request): string
    {
        $redirectRouteName = $request->query->get(static::PARAM_REDIRECT_ROUTE_NAME);

        if (is_string($redirectRouteName) && $redirectRouteName !== '') {
            return $this->router->generate($redirectRouteName);
        }

        return (string)$request->headers->get('Referer', '/');
    }

    /**
     * @return array<string>
     */
    protected function getProtectedRoutes(): array
    {
        return [
            CartCodeWidgetRouteProviderPlugin::ROUTE_NAME_CART_CODE_ADD,
            CartCodeAsyncWidgetRouteProviderPlugin::ROUTE_NAME_CART_CODE_ASYNC_ADD,
        ];
    }
}
