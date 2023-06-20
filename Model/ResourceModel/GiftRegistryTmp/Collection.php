<?php
namespace Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'gift_id_tmp';

    public function _construct()
    {
        $this->_init("Magenest\GiftRegistry\Model\GiftRegistryTmp", "Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp");
    }
}
