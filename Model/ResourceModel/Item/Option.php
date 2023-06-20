<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 00:48
 */

namespace Magenest\GiftRegistry\Model\ResourceModel\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Option
 * @package Magenest\GiftRegistry\Model\ResourceModel\Item
 */
class Option extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_item_option', 'option_id');
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

    /**
     * @param $giftItemIds
     * @throws LocalizedException
     */
    public function removeMultiRecordsByGiftItemId($giftItemIds)
    {
        try {
            $size = count($giftItemIds);
            if (!is_array($giftItemIds) && $size == 0) {
                return;
            }
            $collectionIds = implode(', ', $giftItemIds);
            $this->getConnection()->delete(
                $this->getMainTable(),
                "gift_item_id in ({$collectionIds})"
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}

