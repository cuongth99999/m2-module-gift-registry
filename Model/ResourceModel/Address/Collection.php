<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 01:29
 */
namespace Magenest\GiftRegistry\Model\ResourceModel\Address;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Address
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Address', 'Magenest\GiftRegistry\Model\ResourceModel\Address');
    }
}
