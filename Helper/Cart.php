<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/04/2016
 * Time: 08:33
 */

namespace Magenest\GiftRegistry\Helper;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\OptionFactory;

/**
 * Class Cart
 * @package Magenest\GiftRegistry\Helper
 */
class Cart extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var CustomerRepository
     */
    protected $_customerRepo;

    /**
     * @var Quote\ItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var OptionFactory
     */
    protected $_optionFactory;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @var GiftRegistry
     */
    protected $_giftRegistryResources;

    /**
     * @var Address
     */
    protected $_addressResources;

    /**
     * Cart constructor.
     * @param Context $context
     * @param Session $session
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param GiftRegistryFactory $giftRegistry
     * @param ItemFactory $item
     * @param \Magenest\GiftRegistry\Model\Item\OptionFactory $itemOptionGiftRegistryFactory
     * @param Quote $quote
     * @param Quote\ItemFactory $quoteItemFactory
     * @param OptionFactory $optionItemFactory
     * @param CustomerRepository $customerRepository
     * @param AddressFactory $addressFactory
     * @param ProductMetadataInterface $productMetadata
     * @param Json $serialize
     * @param GiftRegistry $giftRegistryResources
     * @param Address $addressResources
     */
    public function __construct(
        Context $context,
        Session $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        GiftRegistryFactory $giftRegistry,
        ItemFactory $item,
        \Magenest\GiftRegistry\Model\Item\OptionFactory $itemOptionGiftRegistryFactory,
        Quote $quote,
        Quote\ItemFactory $quoteItemFactory,
        OptionFactory $optionItemFactory,
        CustomerRepository $customerRepository,
        AddressFactory $addressFactory,
        ProductMetadataInterface $productMetadata,
        Json $serialize,
        GiftRegistry $giftRegistryResources,
        Address $addressResources
    ) {
        parent::__construct($context);
        $this->_customerSession = $session;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_itemFactory = $item;
        $this->_customerRepo = $customerRepository;
        $this->_quote = $quote;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_addressFactory = $addressFactory;
        $this->_optionFactory = $optionItemFactory;
        $this->productMetadata = $productMetadata;
        $this->serialize = $serialize;
        $this->_giftRegistryResources = $giftRegistryResources;
        $this->_addressResources = $addressResources;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isForGiftRegistry()
    {
        return (bool)$this->getRegistryId();
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getNumberItemsInQuote()
    {
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();
        return count($items);
    }

    /**
     * @return int|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRegistryId()
    {
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();

        if (count($items) > 0) {
            foreach ($items as $item) {
                $itemId = $item->getId();

                $option = $this->_optionFactory->create()->getCollection()->addFieldToFilter('item_id', $itemId)
                    ->addFieldToFilter('code', 'info_buyRequest')->getFirstItem();
                $buyRequestArray = null;

                if ($this->checkMagentoVersion()) {
                    //Magento2.2
                    $buyRequestArray = new DataObject($this->serialize->unserialize($option->getData('value')));
                } else {
                    $buyRequestArray = $this->serialize->unserialize($option->getData('value'));
                }

                if (isset($buyRequestArray['registry'])) {
                    $registry = $buyRequestArray['registry'];
                    break;
                }
            }
        }

        return isset($registry) ? $registry : false;
    }

    /**
     * @return \Magento\Customer\Model\Address
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRegistryAddress()
    {
        $registry = $this->getRegistryId();
        $customerAddress = $this->_addressFactory->create();

        if ($registry) {
            $giftRegistry = $this->_giftRegistryFactory->create();
            $this->_giftRegistryResources->load($giftRegistry, $registry);
            $registryAdd = $giftRegistry->getData('shipping_address');
            $this->_addressResources->load($customerAddress, $registryAdd);
        }

        return $customerAddress;
    }

    /**
     * @return bool
     */
    public function checkMagentoVersion()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, "2.2.0", ">=")) {
            return true;
        }
        return false;
    }
}
