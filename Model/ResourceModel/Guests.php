<?php
/*
 * Created by Magenest
 * Date: 23/03/2020
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Guests
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Guests extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_guest', 'guest_id');
    }
}
