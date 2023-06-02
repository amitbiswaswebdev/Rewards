<?php
/**
 * Copyright © 2023 EasyMage. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    Amit Biswas <amit.biswas.webdev@gmail.com>
 * @copyright 2023 EasyMage
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace EasyMage\Rewards\Controller\Discount;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use EasyMage\Rewards\Api\Discount\RewardInterface;

/**
 * Summary of RewardPost
 */
class RewardPost implements HttpPostActionInterface
{

    /**
     * @param Validator $formKeyValidator
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param RewardInterface $reward
     */
    public function __construct(
        protected readonly Validator $formKeyValidator,
        protected readonly RequestInterface $request,
        protected readonly RedirectFactory $resultRedirectFactory,
        protected readonly RedirectInterface $redirect,
        protected readonly ManagerInterface $messageManager,
        protected readonly CustomerSession $customerSession,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly RewardInterface $reward
    ) {
    }

    /**
     * Summary of execute
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $message = $this->validateAction();
            if ($message) {
                $this->messageManager->addErrorMessage(__($message));
            } else {
                $customerId = $this->customerSession->getCustomerId();
                $params = $this->getRequest()->getPostValue();
                if (array_key_exists('remove', $params)) {
                    if ($params['remove']) {
                        $this->reward->removeByCustomerId((int) $customerId);
                        $this->messageManager->addSuccessMessage(__("Reward points removed from your cart."));
                    } else {
                        $this->reward->applyByCustomerId((int) $customerId);
                        $this->messageManager->addSuccessMessage(__("Reward points applied to your cart."));
                    }
                } else {
                    throw new \RuntimeException('Bad request. Parameter does not exists');
                }
            }
        } catch (\Throwable $th) {
            //TODO:implement log
            $this->messageManager->addErrorMessage(__("Something went wrong. Please try again later."));
        }

        return $this->redirect();
    }

    /**
     * Summary of getRequest
     *
     * @return RequestInterface|Validator
     */
    protected function getRequest(): Validator|RequestInterface
    {
        return $this->request;
    }

    /**
     * Summary of redirect
     *
     * @param ?string $url
     * @return Redirect
     */
    protected function redirect(?string $url = null): Redirect
    {
        if (!$url) {
            $url = $this->redirect->getRefererUrl();
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }

    /**
     * Summary of validateAction
     *
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function validateAction(): ?string
    {
        $errorMessage = null;
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = "Reward points can not be applied due to violation of a security.";
        } elseif (!$this->customerSession->isLoggedIn()) {
            $errorMessage = "Reward points are available for logged in customers only.";
        } elseif ($this->checkoutSession->getQuote()->hasQuote()) {
            $errorMessage = "No active cart found.";
        }

        return $errorMessage;
    }
}