<?php
namespace Magenest\GiftRegistry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class GiftRegistryTmp
 * @package Magenest\GiftRegistry\Model\ResourceModel
 */
class GiftRegistryTmp extends AbstractDb
{
    public function _construct()
    {
        $this->_init("magenest_giftregistry_tmp", "gift_id_tmp");
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
