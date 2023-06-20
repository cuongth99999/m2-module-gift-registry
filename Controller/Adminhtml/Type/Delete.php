<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:40
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Delete extends Type
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage(__('Please select type(s).'));
        } else {
            try {
                $typeModel = $this->_typeFactory->create();
                $this->_typeResource->load($typeModel, $id);
                $code = $typeModel->getData('event_type');
                if ($this->checkGiftRegistry($code)) {
                    $this->messageManager->addErrorMessage(__('You must remove all Gift Registries have event type is %1', $code));
                } else {
                    $this->_typeResource->delete($typeModel);
                    $this->messageManager->addSuccessMessage(
                        __('A total of 1 record have been deleted.')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
