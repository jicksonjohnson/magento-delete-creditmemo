<?php
/**
 * HelloMage
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us jicksonkoottala@gmail.com
 *
 * @category   HelloMage
 * @package    HelloMage_DeleteCreditmemo
 * @copyright  Copyright (C) 2020 HELLOMAGE PVT LTD (https://www.hellomage.com/)
 * @license    https://www.hellomage.com/magento2-osl-3-0-license/
 */

declare(strict_types=1);

namespace HelloMage\DeleteCreditmemo\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Redirect
 * @package HelloMage\DeleteCreditmemo\Model\Config\Source
 */
class Redirect implements ArrayInterface
{
    /**
     * @return array|\string[][]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'credit-memo listing',
                'value' => 'credit-memo-listing'
            ],
            [
                'label' => 'order view',
                'value' => 'order-view'
            ],
            [
                'label' => 'sales order listing',
                'value' => 'order-listing'
            ]
        ];
    }
}