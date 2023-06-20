<?php

/**
 * Created by Magenest.
 * User: trongpq
 * Date: 1/16/18
 * Time: 11:01
 * Email: trongpq@magenest.com
 */
namespace Magenest\GiftRegistry\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Magenest\GiftRegistry\Ui\Component\Listing\Column
 */
class Status implements OptionSourceInterface
{
    const STATUS_ACTIVE = 1;

    const STATUS_INACTIVE = 2;

    /**
     * Retrieve option array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Enabled'),
                'value' => self::STATUS_ACTIVE,
            ],
            [
                'label' => __('Disabled'),
                'value' => self::STATUS_INACTIVE,
            ]
        ];
    }
}
