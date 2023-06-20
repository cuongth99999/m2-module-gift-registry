<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 08:57
 */
namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Tran
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Tran extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_order', 'id');
    }
}
