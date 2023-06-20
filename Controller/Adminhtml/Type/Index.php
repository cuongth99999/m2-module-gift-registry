<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:48
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Index extends Type
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
        $resultPage->setActiveMenu('Magenest_GiftRegistry::giftregistry_event_type');
        $resultPage->getConfig()->getTitle()->prepend(__('Event Types'));
        return $resultPage;
    }
}
