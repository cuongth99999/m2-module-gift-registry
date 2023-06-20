<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Item extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_item', 'gift_item_id');
    }

    /**
     * @param $ids
     * @throws LocalizedException
     */
    public function removeMultiRecords($ids)
    {
        try {
            $size = count($ids);
            if (!is_array($ids) && $size == 0) {
                return;
            }
            $collectionIds = implode(', ', $ids);
            $this->getConnection()->delete(
                $this->getMainTable(),
                "{$this->getIdFieldName()} in ({$collectionIds})"
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}

