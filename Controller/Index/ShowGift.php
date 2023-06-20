<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:35
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\TypeFactory as TypeFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ShowGift
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ShowGift extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var PageFactory
     */
    protected $resultPage;

    /**
     * ShowGift constructor.
     * @param TypeFactory $typeFactory
     * @param \Magento\Framework\Registry $registry
     * @param Context $context
     * @param Data $data
     * @param PageFactory $pageFactory
     * @param Session $customerSession
     * @param GiftRegistryFactory $registryFactory
     */
    public function __construct(
        TypeFactory $typeFactory,
        \Magento\Framework\Registry $registry,
        Context $context,
        Data $data,
        PageFactory $pageFactory,
        Session $customerSession,
        GiftRegistryFactory $registryFactory
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_coreRegistry = $registry;
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        $this->_registryFactory = $registryFactory;
        return parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getRequest()->getParam('type', null);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return Redirect|Page|PageFactory
     */
    public function execute()
    {
        if ($this->getType() && $type = $this->checkEvent($this->getType())) {
            $this->_coreRegistry->register('type', $type->getData('event_type'));
//            if ($this->getCustomerId()) {
//                $this->checkCustomerRegistry();
//            }
            $this->resultPage =  $this->_pageFactory->create();
            $this->resultPage->getConfig()->getTitle()->set(__($type->getData('event_title')));
            return $this->resultPage;
        } else {
            $this->_coreRegistry->unregister('gift_id');
            $this->_coreRegistry->unregister('type');
            if (!$this->getType()) {
                $this->messageManager->addNoticeMessage(__("Something went wrong with your accession!"));
            }
            $this->resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/index/listgift");
            return $this->resultPage;
        }
    }

    /**
     * @param $type
     * @return false|DataObject
     */
    public function checkEvent($type)
    {
        $typeCollection = $this->_typeFactory->create()->getCollection()->addFieldToFilter('event_type', $type);
        if ($typeCollection->getSize() && $typeCollection->getFirstItem()->getData('status')) {
            return $typeCollection->getFirstItem();
        } else {
            $this->messageManager->addNoticeMessage(__("This event has no longer exists or has been disabled!"));
            return false;
        }
    }

    /**
     *
     */
    public function checkCustomerRegistry()
    {
        $registryCollection = $this->_registryFactory->create()->getCollection()->addFieldToFilter('type', $this->getType())->addFieldToFilter('customer_id', $this->getCustomerId());
        if ($registryCollection->count() > 0) {
            $this->_coreRegistry->register('gift_id', $registryCollection->getFirstItem()->getData('gift_id'));
            $this->messageManager->addNoticeMessage(__("We only support one event per type at the same time!"));
        }
    }
}
