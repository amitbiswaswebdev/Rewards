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

namespace EasyMage\Rewards\Api\Discount;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Reward management service interface.
 * @api
 * @since 1
 */
interface RewardInterface
{
    /**
     *  Adds a reward to a specified cart.
     *
     * @param int $quoteId  The cart ID
     * @return bool True if the reward was added
     * @throws RuntimeException If customer not logged in
     * @throws LocalizedException|NoSuchEntityException Error fetching cart|customer value
     **/
    public function apply(int $quoteId): bool;

    /**
     * Deletes a reward from a specified cart.
     *
     * @param int $quoteId The cart ID
     * @return bool
     * @throws \RuntimeException Customer has to log in.
     */
    public function cancel(int $quoteId): bool;

    /**
     * Summary of earnFromAnOrder
     *
     * @param OrderInterface $order
     * @return void
     */
    public function earnFromAnOrder(OrderInterface $order): void;
}