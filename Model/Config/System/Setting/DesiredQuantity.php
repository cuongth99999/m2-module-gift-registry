<?php
namespace Magenest\GiftRegistry\Model\Config\System\Setting;

/**
 * Class DesiredQuantity
 * @package Magenest\GiftRegistry\Model\Config\System\Setting
 */
class DesiredQuantity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __("Drop-down list")
            ],
            [
                'value' => '1',
                'label' => __("Text field")
            ],
        ];
    }
}
