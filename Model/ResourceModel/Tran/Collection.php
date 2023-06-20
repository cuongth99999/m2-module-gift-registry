<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 08:57
 */
namespace Magenest\GiftRegistry\Model\ResourceModel\Tran;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\Tran
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\Tran', 'Magenest\GiftRegistry\Model\ResourceModel\Tran');
    }
}
