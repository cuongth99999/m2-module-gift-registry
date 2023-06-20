<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class MassDelete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class MassDelete extends Type
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $collections = $this->_filer->getCollection($this->_typeCollectionFactory->create());
            $count = 0;
            $ids = [];
            foreach ($collections->getItems() as $item) {
                $code = $item->getData('event_type');
                if ($this->checkGiftRegistry($code)) {
                    throw new \Exception(__('You must remove all Gift Registries have event type is %1', $code));
                }
                $ids[] = $item->getId();
                $count++;
            }
            $this->_typeResource->removeMultiRecords($ids);
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $count)
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
