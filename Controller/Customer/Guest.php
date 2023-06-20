<?php
/**
 * Created by Magenest
 * Date: 20/03/2020
 * Time: 10:39
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GuestsFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Guests;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\StoreManager;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Guest extends AbstractAction
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var GuestsFactory
     */
    protected $_guestsFactory;

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Guests
     */
    protected $_guestsResource;

    /**
     * Guest constructor.
     * @param Context $context
     * @param Data $data
     * @param Session $session
     * @param GuestsFactory $guestsFactory
     * @param StoreManager $storeManager
     * @param CustomerFactory $customerFactory
     * @param ResultJsonFactory $resultJsonFactory
     * @param Guests $guestsResource
     */
    public function __construct(
        Context $context,
        Data $data,
        Session $session,
        GuestsFactory $guestsFactory,
        StoreManager $storeManager,
        CustomerFactory $customerFactory,
        ResultJsonFactory $resultJsonFactory,
        Guests $guestsResource
    ) {
        $this->_context = $context;
        $this->_customerSession = $session;
        $this->_guestsFactory = $guestsFactory;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_guestsResource = $guestsResource;
        parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Layout
     */
    public function execute()
    {
        try {
            if (!$this->_customerSession->isLoggedIn()) {
                $this->messageManager->addWarningMessage(__('Please login to continue.'));
                $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                $resultForward->forward('customer/account/login');
                return $resultForward;
            }
            $type = $this->getRequest()->getParam('type');
            if ($type == 'massdelete') {
                $this->delete();
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('There was an error)'));
        }
        $resultPage = $this->resultRedirectFactory->create();
        $resultPage->setUrl($this->_redirect->getRefererUrl());
        return $resultPage;
    }

    /**
     * @return void
     */
    public function delete()
    {
        $itemIds = $this->getRequest()->getParam('listdelete');
        $qtyItem = 0;
        foreach ($itemIds as $itemId) {
            if ($itemId) {
                try {
                    $item = $this->_guestsFactory->create();
                    $this->_guestsResource->load($item, $itemId);
                    $this->_guestsResource->delete($item);
                    $qtyItem++;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('There is error while deleting guests'));
                }
            }
        }
        $this->messageManager->addSuccessMessage(__('You has been deleted ' . $qtyItem . ' guests from the list guests successfully!'));
    }
}
