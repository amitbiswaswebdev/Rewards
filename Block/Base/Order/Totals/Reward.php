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

namespace EasyMage\Rewards\Block\Base\Order\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class Reward extends Template
{
    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Summary of initTotals
     *
     * @return Reward
     */
    public function initTotals(): static
    {
        $order = $this->getSource();

        $rewardAmount = (double) $order->getData('easy_mage_reward_amount');
        if ($rewardAmount !== (double)0) {
            $rewardAmountRow = new DataObject([
                'code' => 'reward_amount',
                'strong' => false,
                'value' => '-' . $this->getSource()->getData('easy_mage_reward_amount'),
                'label' => __('Reward Amount'),
                'area' => '',
            ]);

            $this->getParentBlock()->addTotalBefore($rewardAmountRow, 'discount');
        }

        return $this;
    }
}
