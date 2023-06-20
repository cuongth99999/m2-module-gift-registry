<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 01:28
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Address extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_shipping_address', 'id');
    }
}
