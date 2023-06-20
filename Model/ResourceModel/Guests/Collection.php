<?php
/*
 * Created by Magenest
 * Date: 23/03/2020
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Guests;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Guests
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'guest_id';

    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Guests', 'Magenest\GiftRegistry\Model\ResourceModel\Guests');
    }
}
