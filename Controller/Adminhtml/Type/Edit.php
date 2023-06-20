<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 11:35
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Edit extends Type
{

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_GiftRegistry::type')
            ->addBreadcrumb(__('Manage Event Type'), __('Manage Event Type'));
        return $resultPage;
    }

    /**
     * Edit Mapping
     *
     * @return  Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_typeFactory->create();

        if ($id) {
            $this->_typeResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This mapping no longer exists.'));
                /**
                 * \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('type', $model);
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __("Edit Event Type '%1'", $model->getEventTitle()) : __('New Event Type'));
        return $resultPage;
    }
}
