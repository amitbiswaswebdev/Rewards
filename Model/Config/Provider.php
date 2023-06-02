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

namespace EasyMage\Rewards\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use EasyMage\Rewards\Model\Config as RewardsConfig;

class Provider implements ConfigProviderInterface
{
    /**
     * Summary of __construct
     *
     * @param RewardsConfig $rewardsConfig
     */
    public function __construct(protected readonly RewardsConfig $rewardsConfig)
    {
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return [
            'reward' => [
                'isEnabled' => $this->rewardsConfig->isEnabled()
            ]
        ];
    }
}
