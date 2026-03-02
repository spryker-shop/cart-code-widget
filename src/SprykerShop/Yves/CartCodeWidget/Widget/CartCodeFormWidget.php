<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CartCodeWidget\Widget;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;
use Symfony\Component\Form\FormView;

/**
 * @method \SprykerShop\Yves\CartCodeWidget\CartCodeWidgetFactory getFactory()
 */
class CartCodeFormWidget extends AbstractWidget
{
    public function __construct(QuoteTransfer $quoteTransfer)
    {
        $this->addParameter('cartCodeForm', $this->getCartCodeFormView());
        $this->addParameter('isQuoteEditable', $this->getIsQuoteEditableParameter($quoteTransfer));
    }

    public static function getName(): string
    {
        return 'CartCodeFormWidget';
    }

    public static function getTemplate(): string
    {
        return '@CartCodeWidget/views/cart-code-form/cart-code-form.twig';
    }

    protected function getIsQuoteEditableParameter(QuoteTransfer $quoteTransfer): bool
    {
        return $this->getFactory()
            ->getQuoteClient()
            ->isQuoteEditable($quoteTransfer);
    }

    protected function getCartCodeFormView(): FormView
    {
        return $this->getFactory()
            ->getCartCodeForm()
            ->createView();
    }
}
