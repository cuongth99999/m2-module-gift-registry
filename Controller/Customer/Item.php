<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 20/04/2016
 * Time: 10:39
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\AddressFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item as ItemResources;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Item extends AbstractAction
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
     * @var AddressFactory
     */
    protected $addressFactory;

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
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var OptionFactory
     */
    protected $_optionFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ItemResources
     */
    protected $_itemResources;

    /**
     * Item constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param RegistrantFactory $registrantFactory
     * @param AddressFactory $addressFactory
     * @param OptionFactory $optionFactory
     * @param Context $context
     * @param Data $data
     * @param Session $session
     * @param CurrentCustomer $currentCustomer
     * @param ItemFactory $itemFactory
     * @param ItemResources $itemResources
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        RegistrantFactory $registrantFactory,
        AddressFactory $addressFactory,
        OptionFactory $optionFactory,
        Context $context,
        Data $data,
        Session $session,
        CurrentCustomer $currentCustomer,
        ItemFactory $itemFactory,
        ItemResources $itemResources,
        PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->registrantFactory = $registrantFactory;
        $this->addressFactory = $addressFactory;
        $this->_optionFactory = $optionFactory;
        $this->_itemFactory = $itemFactory;
        $this->_itemResources = $itemResources;
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarningMessage(__('Please login to continue.'));
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('customer/account/login');
            return $resultForward;
        }

        /**
        * @var Redirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');
        if ($type== 'massdelete') {
            $this->delete();
        } else {
            $this->save();
        }
        $giftID = $this->getRequest()->getParam('event_id');
        $tab = $this->getRequest()->getParam('giftregistry_tab');
        $link = 'gift-registry/manage/' . $tab;
        if ($type!== 'massdelete') {
            $resultRedirect->setPath($link, ['id' => $giftID]);
        } else {
            $resultRedirect->setPath($link, ['id' => $id]);
        }
        return $resultRedirect;
    }

    /**
     * @return void
     */
    public function delete()
    {
        $itemIds=$this->getRequest()->getParam('listdelete');
        $qtyItem = 0;
        foreach ($itemIds as $itemId) {
            if ($itemId) {
                try {
                    $item = $this->_itemFactory->create();
                    $this->_itemResources->load($item, $itemId);
                    $this->_itemResources->delete($item);
                    //delete item option
                    $options = $this->_optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId);
                    foreach ($options as $option) {
                        $option->delete();
                    }
                    $qtyItem++;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('There is error while deleting item(s)'));
                }
            }
        }
        $this->messageManager->addSuccessMessage(__('You has been deleted ' . $qtyItem . ' items from the registry successfully!'));
    }

    /**
     * @return void
     */
    public function save()
    {
        $items = $this->getRequest()->getParam('item');
        $giftItemId = $this->getRequest()->getParam('giftItemId');
        $priority = $this->getRequest()->getParam('priority');
        try {
            if ($items) {
                foreach ($items as $key => $value) {
                    //key is item id
                    //value is $value['note'], $value['qty', $value['priority']

                    $item = $this->_itemFactory->create();
                    $this->_itemResources->load($item, $key);
                    $value['qty'] += $item->getReceivedQty();
                    $item->addData($value);
                    $this->_itemResources->save($item);
                }
                $this->messageManager->addSuccessMessage(__('You updated the gift registry items successfully.'));
            }
            if ($giftItemId != null && $priority != null) {
                $item = $this->_itemFactory->create();
                $this->_itemResources->load($item, $giftItemId);
                $detail = $item->getData();
                $detail['priority'] = $priority;
                $item->setData($detail);
                $this->_itemResources->save($item);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('There is error while updating item(s)'));
        }
    }
}
