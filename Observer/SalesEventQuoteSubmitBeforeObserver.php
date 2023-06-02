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

namespace EasyMage\Rewards\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Gift Message Observer Model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * Set gift messages to order from quote address
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): static
    {
        $observer->getEvent()->getOrder()->setData(
            'easy_mage_reward_amount',
            $observer->getEvent()->getQuote()->getData('easy_mage_reward_amount')
        );
        $observer->getEvent()->getOrder()->setData(
            'easy_mage_reward_params',
            $observer->getEvent()->getQuote()->getData('easy_mage_reward_params')
        );

        return $this;
    }
}