<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 00:48
 */
namespace Magenest\GiftRegistry\Model\ResourceModel\Item\Option;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Item\Option
 */
class Collection extends \Magento\Wishlist\Model\ResourceModel\Item\Option\Collection
{
    /**
     * Define resource model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Item\Option', 'Magenest\GiftRegistry\Model\ResourceModel\Item\Option');
    }
}
