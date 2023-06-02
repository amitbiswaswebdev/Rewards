<?php
/**
 * Copyright © 2023 EasyMage. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author Amit Biswas <amit.biswas.webdev@gmail.com>
 * @copyright 2023 EasyMage
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace EasyMage\Rewards\Model\Service;

use InvalidArgumentException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Quote\Api\CartRepositoryInterface;
use EasyMage\Rewards\Model\Config as RewardsConfig;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use EasyMage\Rewards\Api\Discount\RewardInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Session\SessionManager as CheckoutSession;
use RuntimeException;

/**
 * Apply or Remove reward for customer cart
 */
class Reward implements RewardInterface
{
    /**
     * Summary of __construct
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartRepositoryInterface $cartRepository
     * @param SerializerInterface $serializer
     * @param RewardsConfig $rewardsConfig
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        protected readonly CustomerRepositoryInterface $customerRepository,
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly SerializerInterface $serializer,
        protected readonly RewardsConfig $rewardsConfig,
        protected readonly CheckoutSession $checkoutSession
    ) {
    }

    /**
     * Apply reward to cart
     *
     * @param int $quoteId
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function apply(int $quoteId): bool
    {
        $cart = $this->cartRepository->get($quoteId);
        if ($cart->getCustomerIsGuest()) {
            throw new RuntimeException('You need to login before you apply reward to cart.');
        }
        $customerId = (int) $cart->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $customerRewardPointBalance = (int) $customer->getCustomAttribute('reward_point')->getValue();
        $applicablePoints = $this->applyToCart($cart, $customerRewardPointBalance);
        $this->adjustCustomerBalance($customer, $customerRewardPointBalance, $applicablePoints);

        return true;
    }

    /**
     * Remove reward from cart
     *
     * @param int $quoteId
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function cancel(int $quoteId): bool
    {
        $cart = $this->cartRepository->get($quoteId);
        if ($cart->getCustomerIsGuest()) {
            throw new RuntimeException('You need to login before you apply reward to cart.');
        }
        $customerId = (int) $cart->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $customerRewardPointBalance = (int) $customer->getCustomAttribute('reward_point')->getValue();
        $revokedPoints = $this->removeFromCart($cart);
        $this->adjustCustomerBalance($customer, $customerRewardPointBalance, (-1 * $revokedPoints));

        return true;
    }

    /**
     * Summary of apply
     *
     * @param int $customerId
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyByCustomerId(int $customerId): void
    {
        $customer = $this->customerRepository->getById($customerId);
        $customerRewardPointBalance = (int) $customer->getCustomAttribute('reward_point')->getValue();
        $quote = $this->cartRepository->getActiveForCustomer($customerId);
        $applicablePoints = $this->applyToCart($quote, $customerRewardPointBalance);
        $this->adjustCustomerBalance($customer, $customerRewardPointBalance, $applicablePoints);
    }

    /**
     * Summary of removeByCustomerId
     *
     * @param int $customerId
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function removeByCustomerId(int $customerId): void
    {
        $customer = $this->customerRepository->getById($customerId);
        $customerRewardPointBalance = (int) $customer->getCustomAttribute('reward_point')->getValue();
        $quote = $this->cartRepository->getActiveForCustomer($customerId);
        $revokedPoints = $this->removeFromCart($quote);
        $this->adjustCustomerBalance($customer, $customerRewardPointBalance, (-1 * $revokedPoints));
    }

    /**
     * Summary of removeFromCart
     *
     * @param CartInterface $quote
     * @throws RuntimeException
     * @return int
     */
    protected function removeFromCart(CartInterface $quote): int
    {
        $serializeRewardInfo = $quote->getData('easy_mage_reward_params');
        if ($serializeRewardInfo) {
            $rewardInfo = $this->serializer->unserialize($serializeRewardInfo);
            if (array_key_exists('applied_points', $rewardInfo)) {
                $appliedPoints = $rewardInfo['applied_points'];
                $quote->setData('easy_mage_reward_amount', (float) 0);
                $quote->setData('easy_mage_reward_params', null);
                $this->cartRepository->save($quote);

                return (int) $appliedPoints;
            } else {
                // TODO add log
                throw new RuntimeException('Applied points not found.');
            }
        } else {
            // TODO add log
            throw new RuntimeException('Reward info in cart not found.');
        }
    }

    /**
     * Summary of applyToCart
     *
     * @param CartInterface $quote
     * @param int $customerRewardPointBalance
     * @return int
     */
    protected function applyToCart(CartInterface $quote, int $customerRewardPointBalance): int
    {
        $maxDiscountPercentage = $this->rewardsConfig->getMaxDiscountPercentage();
        if (!$maxDiscountPercentage) {
            // TODO add log
            throw new InvalidArgumentException('Discount percentage in config not set.');
        }

        $pointEquivalentPercentage = $this->rewardsConfig->getPointEquivalentPercentage();
        if (!$pointEquivalentPercentage) {
            // TODO add log
            throw new InvalidArgumentException('Point equivalent percentage not set.');
        }

        $maxPoint = $maxDiscountPercentage / $pointEquivalentPercentage;
        $applicablePoints = (int) ($customerRewardPointBalance <= $maxPoint) ? $customerRewardPointBalance : $maxPoint;
        $rewardDiscountPerCent = $pointEquivalentPercentage * $applicablePoints;
        $subtotal = $quote->collectTotals()->getSubtotal();
        $rewardDiscount = $subtotal * ($rewardDiscountPerCent / 100);
        $rewardInfo = [
            'applied_points' => $applicablePoints,
            'max_discount_percentage' => $maxDiscountPercentage,
            'point_equivalent_percentage' => $pointEquivalentPercentage,
            'subtotal' => $subtotal
        ];
        $serializeRewardInfo = $this->serializer->serialize($rewardInfo);
        $quote->setData('easy_mage_reward_amount', $rewardDiscount);
        $quote->setData('easy_mage_reward_params', $serializeRewardInfo);
        $this->cartRepository->save($quote);

        return $applicablePoints;
    }

    /**
     * Summary of adjustCustomerBalance
     *
     * @param CustomerInterface $customer
     * @param int $customerRewardPointBalance
     * @param int $pointsApplied
     * @return void
     * @throws LocalizedException
     * @throws InputException
     * @throws InputMismatchException
     */
    protected function adjustCustomerBalance(
        CustomerInterface $customer,
        int $customerRewardPointBalance,
        int $pointsApplied
    ): void {
        $customerRewardPointBalance -= $pointsApplied;
        $customer->setCustomAttribute('reward_point', $customerRewardPointBalance);
        $this->customerRepository->save($customer);
    }

    /**
     * Summary of earnFromAnOrder
     *
     * @param OrderInterface $order
     * @return void
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function earnFromAnOrder(OrderInterface $order): void
    {
        $websiteId = (int) $order->getStore()->getWebsiteId();
        if ($this->rewardsConfig->isEnabled($websiteId)) {
            $isRewardOnOrderEnabled = $this->rewardsConfig->isEnabledOnOrder($websiteId);
            if ($isRewardOnOrderEnabled) {
                $customerId = $order->getCustomerId();
                if ($customerId !== null) {
                    $rewardUnit = $this->rewardsConfig->rewardPointOnOrder($websiteId);
                    $rewardUnit = ($rewardUnit > 0) ? $rewardUnit : 0;
                    $customer = $this->customerRepository->getById($customerId);
                    $customerRewardPointBalance = $customer->getCustomAttribute('reward_point')->getValue();
                    $orderTotal = $order->getGrandTotal();
                    $earnedRewardPoint = round($orderTotal / $rewardUnit);
                    $customerRewardPointBalance += $earnedRewardPoint;
                    $customer->getCustomAttribute('reward_point')->setValue($customerRewardPointBalance);
                    $this->checkoutSession->setLastEarnedRewardPoint($earnedRewardPoint);
                    $this->customerRepository->save($customer);
                }
            }
        }
    }
}