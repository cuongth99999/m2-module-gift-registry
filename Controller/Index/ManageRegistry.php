<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:39
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\TypeFactory as TypeFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ManageRegistry
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ManageRegistry extends AbstractAction
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
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /** @var GiftRegistry $_registryResource */
    protected $_registryResource;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var Type
     */
    protected $_typeResource;

    /**
     * @var PageFactory
     */
    protected $resultPage;

    /**
     * ReigstryView constructor.
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param Session $session
     * @param \Magento\Framework\Registry $registry
     * @param LoggerInterface $logger
     * @param GiftRegistryFactory $registryFactory
     * @param GiftRegistry $registryResource
     * @param Context $context
     * @param Data $data
     * @param PageFactory $pageFactory
     */
    public function __construct(
        TypeFactory $typeFactory,
        Type $typeResource,
        Session $session,
        \Magento\Framework\Registry $registry,
        LoggerInterface $logger,
        GiftRegistryFactory $registryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry $registryResource,
        Context $context,
        Data $data,
        PageFactory $pageFactory
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->_registryFactory = $registryFactory;
        $this->_registryResource = $registryResource;
        $this->_logger = $logger;
        $this->_customerSession = $session;
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $registry;
        return parent::__construct($context, $data);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            try {
                if (!$this->getCustomerId()) {
                    $this->messageManager->addWarningMessage(__('Please login to continue'));
                    $this->_customerSession->setRegistryLogin(true);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('customer/account/login');
                }
                $giftregistryModel = $this->_registryFactory->create();
                $this->_registryResource->load($giftregistryModel, $params['id']);
                if (!$giftregistryModel->getId()) {
                    throw new \Exception(__("This Gift Registry has no longer exists."));
                }
                $type = $giftregistryModel->getData('type');
                $typeModel = $this->_typeFactory->create();
                $this->_typeResource->load($typeModel, $type, 'event_type');
                if (!$typeModel->getId()||$typeModel->getData('status') != '1') {
                    throw new \Exception(__("This event has no longer exists or has been disabled!"));
                }

                $customerId = $this->getCustomerId();
                if ($customerId != $giftregistryModel->getData('customer_id')) {
                    throw new \Exception(__("You have not created this event!"));
                }

                $resultPage =  $this->_pageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__($giftregistryModel->getData('title')));
                return $resultPage;
            } catch (\Exception $exception) {
                $this->messageManager->addNoticeMessage($exception->getMessage());
                $resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/customer/mygiftregistry");
                return $resultPage;
            }
        } else {
            $this->messageManager->addNoticeMessage(__("Something went wrong with your accession!"));
            $resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/index/listgift");
            return $resultPage;
        }
    }
}
