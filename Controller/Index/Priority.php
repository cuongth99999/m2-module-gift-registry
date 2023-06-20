<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Priority
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Priority extends IndexAbstract
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (array_key_exists("gift_id", $params)&&array_key_exists("product_id", $params)) {
            try {
                if (array_key_exists("gift_item_data", $params)) {
                    $giftItemData = $params['gift_item_data'];
                    foreach ($giftItemData as $giftItem) {
                        $itemModel = $this->_itemFactory->create();
                        $this->_itemResource->load($itemModel, $giftItem['gift_item']);
                        if ($itemModel->getId()) {
                            $itemModel->setPriority($giftItem['priority']);
                            $this->_itemResource->save($itemModel);
                            $this->messageManager->addSuccessMessage(__('This item updated to your gift registry.'));
                        } else {
                            throw new \Exception(__('Something went wrong while saving the page. Please refresh the page and try again.'));
                        }
                    }
                } else {
                    $this->messageManager->addNoticeMessage(__('Please validate your data.'));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the page. Please refresh the page and try again.'));
                $this->_logger->critical($exception->getMessage());
            }
        } else {
            $this->messageManager->addNoticeMessage(__("You must chose a gift registry."));
        }
    }
}
