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

namespace EasyMage\Rewards\Model\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use EasyMage\Rewards\Model\Config as RewardsConfig;

class Rewards extends AbstractTotal
{
    /**
     * Summary of rewardAmount
     *
     * @var float|null
     */
    private ?float $rewardAmount = null;

    /**
     * Summary of __construct
     *
     * @param RewardsConfig $rewardsConfig
     */
    public function __construct(protected readonly RewardsConfig $rewardsConfig)
    {
    }

    /**
     * Summary of collect
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Quote\Address\Total $total
     * @return Rewards
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): static {
        parent::collect($quote, $shippingAssignment, $total);
        $this->clearValues($total);
        if (!$quote->getCustomerIsGuest() && $this->rewardsConfig->isEnabled()) {
            $rewardAmount = $this->getRewardAmount($quote);
            if (abs($rewardAmount) !== 0) {
                $total->setTotalAmount('rewards', $rewardAmount);
                $total->setBaseTotalAmount('rewards', $rewardAmount);
                $total->setRewards($rewardAmount);
                $total->setBaseRewards($rewardAmount);
            }

        }

        return $this;
    }

    /**
     * Clear total values
     *
     * @param Total $total
     */
    protected function clearValues(Total $total): void
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('shipping', 0);
        $total->setBaseTotalAmount('shipping', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
        $total->setShippingInclTax(0);
        $total->setBaseShippingInclTax(0);
        $total->setShippingTaxAmount(0);
        $total->setBaseShippingTaxAmount(0);
        $total->setShippingAmountForDiscount(0);
        $total->setBaseShippingAmountForDiscount(0);
        $total->setBaseShippingAmountForDiscount(0);
        $total->setTotalAmount('extra_tax', 0);
        $total->setBaseTotalAmount('extra_tax', 0);
    }
    /**
     * Summary of fetch
     *
     * @param Quote $quote
     * @param Quote\Address\Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $row = [];

        if (!$quote->getCustomerIsGuest() && $this->rewardsConfig->isEnabled()) {
            $rewardAmount = $this->getRewardAmount($quote);
            if (abs($rewardAmount) !== 0) {
                $row = [
                    'code' => 'rewards',
                    'title' => __('Reward discount'),
                    'value' => $rewardAmount
                ];
            }
        }

        return $row;
    }

    /**
     * Summary of getLabel
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Reward discount');
    }

    /**
     * Summary of getRewardAmount
     *
     * @param Quote $quote
     * @return float
     */
    protected function getRewardAmount(Quote $quote): float
    {
        if (!$this->rewardAmount) {
            $pointEquivalentPercentage = $this->rewardsConfig->getPointEquivalentPercentage();
            $maxDiscountPercentage = $this->rewardsConfig->getMaxDiscountPercentage();
            if ($pointEquivalentPercentage && $maxDiscountPercentage) {
                $appliedPointsAmount = (float) $quote->getData('easy_mage_reward_amount');
                $subTotal = (float) $quote->getSubtotal();
                //TODO :: check applied point percentage with max percentage and update the reward if changed
                if ($appliedPointsAmount > 0 && $appliedPointsAmount < $subTotal) {
                    $this->rewardAmount = -1 * $appliedPointsAmount;
                } else {
                    $this->rewardAmount = 0;
                    //TODO :: add log
                }
            } else {
                $this->rewardAmount = 0;
                //TODO :: add log
            }
        }

        return (float) $this->rewardAmount;
    }
}
