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

namespace EasyMage\Rewards\Plugin\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface as QuoteTotalsInterface;

class CartTotalRepository
{
    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsExtensionInterfaceFactory $totalsExtensionInterfaceFactory
     */
    public function __construct(
        protected readonly CartRepositoryInterface $quoteRepository,
        protected readonly TotalsExtensionInterfaceFactory $totalsExtensionInterfaceFactory
    ) {
    }

    /**
     * Summary of afterGet
     *
     * @param CartTotalRepositoryInterface $subject
     * @param QuoteTotalsInterface $result
     * @param int $cartId
     * @return QuoteTotalsInterface
     * @throws NoSuchEntityException
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        QuoteTotalsInterface $result,
        int $cartId
    ): QuoteTotalsInterface {
        $quote = $this->quoteRepository->getActive($cartId);
        $extensionAttributes = $result->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->totalsExtensionInterfaceFactory->create();
        }
        $extensionAttributes->setRewardAmount($quote->getData('easy_mage_reward_amount'));
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
