<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 21/04/2016
 * Time: 14:14
 */
namespace Magenest\GiftRegistry\Observer\Cart;

use Magenest\GiftRegistry\Helper\Cart;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Validator\Exception;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Observer\Cart
 */
class Add implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $_quoteHelper;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var Quote
     */
    protected $_quote;
    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;
    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;
    /**
     * @var ItemFactory
     */
    protected $_itemFactory;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Add constructor.
     * @param ManagerInterface $messageManager
     * @param GiftRegistryFactory $giftRegistry
     * @param ItemFactory $item
     * @param Quote $quote
     * @param Cart $cartHelper
     * @param CustomerRepository $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManagerInterface $messageManager,
        GiftRegistryFactory $giftRegistry,
        ItemFactory $item,
        Quote $quote,
        Cart $cartHelper,
        CustomerRepository $customerRepository,
        LoggerInterface $logger
    ) {
        $this->_messageManager = $messageManager;
        $this->_customerRepository = $customerRepository;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_itemFactory = $item;
        $this->_logger = $logger;
        $this->_quote = $quote;
        $this->_quoteHelper = $cartHelper;
    }

    /**
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $cartForRegistry =  $this->_quoteHelper->isForGiftRegistry();
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        $itemsInQuote = $this->_quoteHelper->getNumberItemsInQuote();
        $registry = $buyRequest->getData('registry');
        $registryId = $this->_quoteHelper->getRegistryId();

        //there are case when there are item but no for any gift registry and there are item and for specific gift registry

        if ($cartForRegistry) {
            if ($registryId != $registry) {
                $this->_messageManager->addWarningMessage(__("You must clear the gift for friend in the cart before buy your item!"));
                throw new Exception(__('You must clear the cart before buy gift for friend.'));
            }
        } else {
            if ($itemsInQuote > 1) {
                if ($registry) {
                    $this->_messageManager->addWarningMessage(__("You must clear the cart before buy gift for your friend!"));
                    throw new Exception(__('You must clear the cart before buy gift for friend.'));
                }
            }
        }
    }
}
