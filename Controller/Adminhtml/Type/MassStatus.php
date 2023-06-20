<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassStatus
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class MassStatus extends Type
{
    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->_filer->getCollection($this->_typeCollectionFactory->create());
        $status = $this->getRequest()->getParam('status');
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->setStatus($status);
            $item->save();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $collectionSize));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
