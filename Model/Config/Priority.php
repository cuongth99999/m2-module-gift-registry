<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:47
 */
namespace Magenest\GiftRegistry\Model\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Priority
 * @package Magenest\GiftRegistry\Model\Config
 */
class Priority implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            0 => __('Like to have'),
            1 => __('Love to have'),
            2 => __('Must have')
        ];
    }
}
