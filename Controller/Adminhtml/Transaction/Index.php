<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:48
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Transaction
 */
class Index extends Action
{
    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_GiftRegistry::giftregistry_order');
        $resultPage->getConfig()->getTitle()->prepend(__('GiftRegistry Orders'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::order');
    }
}
