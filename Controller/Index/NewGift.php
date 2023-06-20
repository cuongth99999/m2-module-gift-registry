<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 11/07/2017
 * Time: 15:31
 */
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class NewRegistry
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class NewGift extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $registryFactory;

    /**
     * @var RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $data;

    /**
     * NewRegistry constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param RegistrantFactory $registrantFactory
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param Session $session
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param Data $data
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        RegistrantFactory $registrantFactory,
        Context $context,
        CurrentCustomer $currentCustomer,
        Session $session,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        Data $data
    ) {
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->registrantFactory = $registrantFactory;
        $this->data = $data;

        parent::__construct($context, $data);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarningMessage(__('Please login to continue.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account/login');
        }
        $resultPage = $this->resultPageFactory->create();
        $giftRegistryType = urldecode($this->getRequest()->getParam('type'));
        $eventType = $this->data->getEventTypeList();
        if (array_search($giftRegistryType, $eventType) === false) {
            $this->messageManager->addWarningMessage(__('This event type has been disabled or deleted.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('gift_registry/customer/mygiftregistry/');
        }
        $this->coreRegistry->register('type', $giftRegistryType);
        $resultPage->getConfig()->getTitle()->set(__('Add New GiftRegistry'));
        return $resultPage;
    }

    /**
     * @param $type
     * @return bool
     */
    public function checkCreated($type)
    {
        $registry = $this->registryFactory->create()->getCollection()->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId())->addFieldToFilter('type', $type);
        if ($registry->count()) {
            return true;
        }
        return false;
    }
}
