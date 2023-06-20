<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\GiftRegistry', 'Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry');
    }

    /**
     * @param $productId
     * @param $customerId
     * @return array
     */
    public function getProductsInGiftRegistry($productId, $customerId)
    {
        $itemTable = $this->getResource()->getTable('magenest_giftregistry_item');
        $sql = $this->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['i' => $itemTable],
                'main_table.gift_id = i.gift_id',
                []
            )->where(
                "`i`.`product_id` = '" . $productId . "' AND `main_table`.`customer_id` = '" . $customerId . "' AND `main_table`.`is_expired` = '0'"
            )->group(
                "main_table.gift_id"
            )->columns([
                'main_table.gift_id as gift_id',
                'main_table.title as title',
                'main_table.type as type',
            ])->__toString();
        return $this->getConnection()->fetchAll($sql);
    }
}
