<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Owner;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Owner
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Owner', 'Magenest\GiftRegistry\Model\ResourceModel\Owner');
    }
}
