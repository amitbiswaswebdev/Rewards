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

namespace EasyMage\Rewards\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use EasyMage\Rewards\Api\Discount\RewardInterface;
use Throwable;

class AddRewardPoint implements ObserverInterface
{

    /**
     * @param RewardInterface $reward
     */
    public function __construct(protected readonly RewardInterface $reward)
    {
    }

    /**
     * Add reward point to customer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        try {
            $order = $observer->getOrder();
            $this->reward->earnFromAnOrder($order);
        } catch (Throwable $th) {
            // TODO:Add log
        }
    }
}
