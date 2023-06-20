<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:24
 */

namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class Type extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_giftregistry_event_type', 'id');
    }

    /**
     * @param $ids
     * @throws \Exception
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
            throw new \Exception(__('Something went wrong while saving the database. Please refresh the page and try again.'));
        }
    }
}
