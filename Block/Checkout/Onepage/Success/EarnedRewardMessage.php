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

namespace EasyMage\Rewards\Block\Checkout\Onepage\Success;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Session\SessionManager as CheckoutSession;
use EasyMage\Rewards\Model\Config as RewardsConfig;

class EarnedRewardMessage extends Template
{
    /**
     * @param Template\Context $context
     * @param CheckoutSession $checkoutSession
     * @param RewardsConfig $rewardsConfig
     * @param array $data
     */
    public function __construct(
        protected readonly Template\Context $context,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly RewardsConfig $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Show Earned Reward on success page
     *
     * @return string
     */
    public function show(): string
    {
        $message = '';
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            $websiteId = (int) $order->getStore()->getWebsiteId();
            if ($this->rewardsConfig->isEnabled($websiteId)) {
                $lastRewardPoint = $this->checkoutSession->getLastEarnedRewardPoint();
                if ($lastRewardPoint) {
                    $messagePhrase = __('Congratulations!! You have own %1 rewards point.', $lastRewardPoint);
                    $message = $messagePhrase->render();
                }
            }
        } catch (\Exception $e) {
            //TODO: add log
        }

        return $message;
    }
}