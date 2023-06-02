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

namespace EasyMage\Rewards\Block\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use EasyMage\Rewards\Model\Config as RewardsConfig;

class Reward extends Template
{
    /**
     * @var int|null
     */
    protected ?int $rewardPoints = null;

    /**
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param RewardsConfig $rewardsConfig
     * @param array $data
     */
    public function __construct(
        protected readonly Template\Context $context,
        protected readonly CustomerSession $customerSession,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly RewardsConfig $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Can show Reward Message
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canShow(): bool
    {
        $canShow = false;
        if (
            $this->rewardsConfig->isEnabled() &&
            $this->customerSession->isLoggedIn() &&
            $this->getRewardPoints() > 0
        ) {
            $canShow = true;
        }

        return $canShow;
    }

    /**
     * Summary of getRewardPoints
     *
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRewardPoints(): ?int
    {
        if ($this->rewardPoints === null) {
            $customerData = $this->customerSession->getCustomerData();
            $points = $customerData->getCustomAttribute('reward_point')->getValue();
            $this->rewardPoints = (is_numeric($points)) ? (int) $points : 0;
        }

        return $this->rewardPoints;
    }

    /**
     * Summary of isRewardApplied
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function isApplied(): bool
    {
        $applied = false;
        if ($this->checkoutSession->hasQuote()) {
            $quote = $this->checkoutSession->getQuote();
            $rewardParam = $quote->getData('easy_mage_reward_params');
            $applied = ($rewardParam !== null);
        }

        return $applied;
    }

    /**
     * Summary of canRemove
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canRemove(): int
    {
        return ($this->isApplied()) ? 1 : 0;
    }
}