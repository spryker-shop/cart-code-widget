<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CartCodeWidget\Controller;

use Generated\Shared\Transfer\CartCodeRequestTransfer;
use Generated\Shared\Transfer\CartCodeResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerShop\Yves\ShopApplication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\CartCodeWidget\CartCodeWidgetFactory getFactory()
 */
abstract class AbstractCodeController extends AbstractController
{
    /**
     * @var string
     */
    protected const PARAM_REDIRECT_ROUTE_NAME = 'redirectRouteName';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_CODE_APPLY_FAILED = 'cart.code.apply.failed';

    /**
     * @uses \Spryker\Shared\CartCode\CartCodesConfig::MESSAGE_TYPE_SUCCESS
     *
     * @var string
     */
    protected const MESSAGE_TYPE_SUCCESS = 'success';

    /**
     * @uses \Spryker\Shared\CartCode\CartCodesConfig::MESSAGE_TYPE_ERROR
     *
     * @var string
     */
    protected const MESSAGE_TYPE_ERROR = 'error';

    protected function createCartCodeRequestTransfer(
        QuoteTransfer $quoteTransfer,
        ?string $code = null
    ): CartCodeRequestTransfer {
        return (new CartCodeRequestTransfer())
            ->setQuote($quoteTransfer)
            ->setCartCode($code);
    }

    protected function processErrorResponseMessage(CartCodeResponseTransfer $cartCodeResponseTransfer): void
    {
        if ($this->successMessageExists($cartCodeResponseTransfer)) {
            return;
        }

        $this->addErrorMessage(static::GLOSSARY_KEY_CODE_APPLY_FAILED);
    }

    protected function successMessageExists(CartCodeResponseTransfer $cartCodeResponseTransfer): bool
    {
        foreach ($cartCodeResponseTransfer->getMessages() as $messageTransfer) {
            if ($messageTransfer->getType() === static::MESSAGE_TYPE_SUCCESS) {
                return true;
            }
        }

        return false;
    }

    protected function redirectResponse(
        CartCodeResponseTransfer $cartCodeResponseTransfer,
        Request $request
    ): RedirectResponse {
        $this->getFactory()->getQuoteClient()->setQuote($cartCodeResponseTransfer->getQuote());
        $this->getFactory()->getZedRequestClient()->addFlashMessagesFromLastZedRequest();

        foreach ($cartCodeResponseTransfer->getMessages() as $messageTransfer) {
            $this->handleMessage($messageTransfer);
        }

        return $this->getRedirectResponse($request);
    }

    protected function getRedirectResponse(Request $request): RedirectResponse
    {
        $redirectRouteName = (string)$request->query->get(static::PARAM_REDIRECT_ROUTE_NAME);

        return $redirectRouteName
            ? $this->redirectResponseInternal($redirectRouteName)
            : $this->redirectResponseExternal($request->headers->get('referer'));
    }

    protected function handleMessage(MessageTransfer $messageTransfer): void
    {
        if ($messageTransfer->getType() === static::MESSAGE_TYPE_ERROR) {
            return;
        }

        switch ($messageTransfer->getType()) {
            case static::MESSAGE_TYPE_SUCCESS:
                $this->addSuccessMessage($messageTransfer->getValue());

                break;
            default:
                $this->addInfoMessage($messageTransfer->getValue());
        }
    }
}
