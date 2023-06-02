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

namespace EasyMage\Rewards\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * XML path constants
     */
    public const XML_PATH_REWARD_IS_ENABLE = "easy_mage/general/enabled";
    public const XML_PATH_REWARD_POINT_EQUIVALENT_PERCENTAGE = "easy_mage/general/point_equivalent_percentage";
    public const XML_PATH_REWARD_MAX_DISCOUNT = "easy_mage/general/max_discount";
    public const XML_PATH_REWARD_IS_ENABLE_ON_ORDER = "easy_mage/order/enabled";
    public const XML_PATH_REWARD_UNIT_ON_ORDER = "easy_mage/order/amount";
    public const XML_PATH_REWARD_DEFAULT_POINT_ON_PRODUCT = "easy_mage/product/amount";
    public const XML_PATH_REWARD_IS_ENABLE_ON_CUSTOMER = "easy_mage/customer/enabled";

    /**
     * Summary of __construct
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(protected readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * Summary of isEnabled
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnabled(int $websiteId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_REWARD_IS_ENABLE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId,
        );
    }

    /**
     * Summary of getPointEquivalentPercentage
     *
     * @param int|null $websiteId
     * @return int|null
     */
    public function getPointEquivalentPercentage(int $websiteId = null): ?int
    {
        return ($this->isEnabled($websiteId)) ? (int) $this->scopeConfig->getValue(
            self::XML_PATH_REWARD_POINT_EQUIVALENT_PERCENTAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId,
        ) : null;
    }

    /**
     * Summary of getMaxDiscountPercentage
     *
     * @param int|null $websiteId
     * @return int|null
     */
    public function getMaxDiscountPercentage(int $websiteId = null): ?int
    {
        return ($this->isEnabled($websiteId)) ? (int) $this->scopeConfig->getValue(
            self::XML_PATH_REWARD_MAX_DISCOUNT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId,
        ) : null;
    }

    /**
     * Summary of isEnabledOnOrder
     *
     * @param int|null $websiteId
     * @return bool
     */
    public function isEnabledOnOrder(int $websiteId = null): bool
    {
        return $this->isEnabled($websiteId) && (bool)$this->scopeConfig->getValue(
            self::XML_PATH_REWARD_IS_ENABLE_ON_ORDER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId,
        );
    }

    /**
     * Summary of rewardPointOnOrderUnit
     *
     * @param int|null $websiteId
     * @return int|null
     */
    public function rewardPointOnOrder(int $websiteId = null): ?int
    {
        return ($this->isEnabled($websiteId) && $this->isEnabledOnOrder($websiteId)) ?
            (int) $this->scopeConfig->getValue(
                self::XML_PATH_REWARD_UNIT_ON_ORDER,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId,
            ) : null;
    }
}
