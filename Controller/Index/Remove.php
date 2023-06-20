<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Item;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
/**
 * Class Remove
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Remove extends IndexAbstract
{
    /**
     * @param Session $session
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param ItemFactory $itemFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item $itemResource
     * @param CollectionFactory $itemCollection
     * @param OptionFactory $optionFactory
     * @param Option $optionResource
     * @param Option\CollectionFactory $optionCollection
     * @param LoggerInterface $logger
     * @param Context $context
     * @param Data $data
     */

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if ($params['gift_id']&&$params['product_id']) {
            $resultRedirect = $this->resultRedirectFactory->create();
            if (array_key_exists("gift_item", $params)&&is_array($params['gift_item'])) {
                try {
                    $this->_itemOptionResource->removeMultiRecordsByGiftItemId($params['gift_item']);
                    $this->_itemResource->removeMultiRecords($params['gift_item']);
                    $this->_cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Item::CACHE_TAG]);
                    $this->messageManager->addSuccessMessage(__('This item deleted to your gift registry.'));
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                }
            } else {
                try {
                    $itemCollections = $this->_itemCollection->create()
                        ->addFieldToFilter('gift_id', $params['gift_id'])
                        ->addFieldToFilter('product_id', $params['product_id']);
                    $gift_item = [];
                    /** @var Item $collection */
                    foreach ($itemCollections as $collection) {
                        $gift_item[] = $collection->getGiftItemId();
                    }
                    $this->_itemOptionResource->removeMultiRecordsByGiftItemId($gift_item);
                    $this->_itemResource->removeMultiRecords($gift_item);
                    $this->_cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Item::CACHE_TAG]);
                    $this->messageManager->addSuccessMessage(__('This item deleted to your gift registry.'));
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                }
            }
            $resultRedirect->setPath(
                'gift-registry/manage/items/id/',
                [
                    'id' => $params['gift_id']
                ]
            );
            return $resultRedirect;
        } else {
            $this->messageManager->addNoticeMessage(__("You must chose a gift registry."));
        }
    }
}

