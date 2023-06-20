<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 13/03/2016
 * Time: 00:36
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class View extends AbstractAction
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * View constructor.
     * @param Context $context
     * @param Data $data
     * @param Session $session
     * @param Registry $registry
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        Session $session,
        Registry $registry,
        CollectionFactory $itemCollectionFactory
    ) {
        $this->coreRegistry = $registry;
        $this->_customerSession = $session;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarningMessage(__('Please login to continue.'));
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('customer/account/login');
            return $resultForward;
        }

        $id = $this->getRequest()->getParam('id');
        $this->coreRegistry->register('registry', $id);
        $items = $this->itemCollectionFactory->create();
        $this->coreRegistry->register('items', $items);
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Gift Registry Items'));
        $this->_view->renderLayout();
    }
}
