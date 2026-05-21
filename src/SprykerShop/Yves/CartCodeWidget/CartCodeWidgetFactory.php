<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CartCodeWidget;

use Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface;
use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\CartCodeWidget\Dependency\Client\CartCodeWidgetToCartCodeClientInterface;
use SprykerShop\Yves\CartCodeWidget\Dependency\Client\CartCodeWidgetToQuoteClientInterface;
use SprykerShop\Yves\CartCodeWidget\Dependency\Client\CartCodeWidgetToZedRequestClientInterface;
use SprykerShop\Yves\CartCodeWidget\EventSubscriber\SecurityBlockerCartCodeEventSubscriber;
use SprykerShop\Yves\CartCodeWidget\Form\CartCodeClearForm;
use SprykerShop\Yves\CartCodeWidget\Form\CartCodeForm;
use SprykerShop\Yves\CartCodeWidget\Form\CartCodeRemoveForm;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

class CartCodeWidgetFactory extends AbstractFactory
{
    public function getCartCodeClient(): CartCodeWidgetToCartCodeClientInterface
    {
        return $this->getProvidedDependency(CartCodeWidgetDependencyProvider::CLIENT_CART_CODE);
    }

    public function getQuoteClient(): CartCodeWidgetToQuoteClientInterface
    {
        return $this->getProvidedDependency(CartCodeWidgetDependencyProvider::CLIENT_QUOTE);
    }

    public function getZedRequestClient(): CartCodeWidgetToZedRequestClientInterface
    {
        return $this->getProvidedDependency(CartCodeWidgetDependencyProvider::CLIENT_ZED_REQUEST);
    }

    public function getCartCodeForm(): FormInterface
    {
        return $this->getFormFactory()->create(CartCodeForm::class);
    }

    public function getCartCodeRemoveForm(): FormInterface
    {
        return $this->getFormFactory()->create(CartCodeRemoveForm::class);
    }

    public function getCartCodeClearForm(): FormInterface
    {
        return $this->getFormFactory()->create(CartCodeClearForm::class);
    }

    public function getFormFactory(): FormFactory
    {
        return $this->getProvidedDependency(ApplicationConstants::FORM_FACTORY);
    }

    public function createSecurityBlockerCartCodeEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerCartCodeEventSubscriber(
            $this->getSecurityBlockerClient(),
            $this->getRouter(),
        );
    }

    public function getSecurityBlockerClient(): SecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(CartCodeWidgetDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function getRouter(): RouterInterface
    {
        return $this->getProvidedDependency(CartCodeWidgetDependencyProvider::SERVICE_ROUTER);
    }
}
