<?php
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Helper\Cart;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\Generic;
use Magento\Multishipping\Helper\Data;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory;
use Magento\Payment\Model\Method\SpecificationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Multishipping
 * @package Magenest\GiftRegistry\Model
 */
class Multishipping extends \Magento\Multishipping\Model\Checkout\Type\Multishipping
{
    /**
     * @var Cart
     */
    private $_quoteHelper;

    /**
     * @var GiftRegistryFactory
     */
    private $_giftRegistryFactory;

    /**
     * @var GiftRegistry
     */
    private $_giftRegistryResources;

    /**
     * @var LoggerInterface|null
     */
    private $_logger;

    /**
     * MultishippingAddresses constructor.
     * @param Cart $quoteHelper
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResources
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param OrderFactory $orderFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param ManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Generic $session
     * @param \Magento\Quote\Model\Quote\AddressFactory $addressFactory
     * @param ToOrder $quoteAddressToOrder
     * @param ToOrderAddress $quoteAddressToOrderAddress
     * @param ToOrderPayment $quotePaymentToOrderPayment
     * @param ToOrderItem $quoteItemToOrderItem
     * @param StoreManagerInterface $storeManager
     * @param SpecificationInterface $paymentSpecification
     * @param Data $helper
     * @param OrderSender $orderSender
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartRepositoryInterface $quoteRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TotalsCollector $totalsCollector
     * @param array $data
     * @param CartExtensionFactory|null $cartExtensionFactory
     * @param AllowedCountries|null $allowedCountryReader
     * @param PlaceOrderFactory|null $placeOrderFactory
     * @param LoggerInterface|null $logger
     * @param DataObjectHelper|null $dataObjectHelper
     */
    public function __construct(
        Cart $quoteHelper,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResources,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        OrderFactory $orderFactory,
        AddressRepositoryInterface $addressRepository,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        Generic $session,
        \Magento\Quote\Model\Quote\AddressFactory $addressFactory,
        ToOrder $quoteAddressToOrder,
        ToOrderAddress $quoteAddressToOrderAddress,
        ToOrderPayment $quotePaymentToOrderPayment,
        ToOrderItem $quoteItemToOrderItem,
        StoreManagerInterface $storeManager,
        SpecificationInterface $paymentSpecification,
        Data $helper,
        OrderSender $orderSender,
        PriceCurrencyInterface $priceCurrency,
        CartRepositoryInterface $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TotalsCollector $totalsCollector,
        array $data = [],
        CartExtensionFactory $cartExtensionFactory = null,
        AllowedCountries $allowedCountryReader = null,
        PlaceOrderFactory $placeOrderFactory = null,
        LoggerInterface $logger = null,
        DataObjectHelper $dataObjectHelper = null
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_giftRegistryResources = $giftRegistryResources;
        $this->_logger = $logger;
        parent::__construct($checkoutSession, $customerSession, $orderFactory, $addressRepository, $eventManager, $scopeConfig, $session, $addressFactory, $quoteAddressToOrder, $quoteAddressToOrderAddress, $quotePaymentToOrderPayment, $quoteItemToOrderItem, $storeManager, $paymentSpecification, $helper, $orderSender, $priceCurrency, $quoteRepository, $searchCriteriaBuilder, $filterBuilder, $totalsCollector, $data, $cartExtensionFactory, $allowedCountryReader, $placeOrderFactory, $logger, $dataObjectHelper);
    }

    /**
     * @param mixed $addressId
     * @return bool
     */
    protected function isAddressIdApplicable($addressId)
    {
        try {
            $giftRegistryId = $this->_quoteHelper->getRegistryId();
            if ($giftRegistryId) {
                $giftRegistry = $this->_giftRegistryFactory->create();
                $this->_giftRegistryResources->load($giftRegistry, $giftRegistryId);
                $giftAddressId = $giftRegistry->getData('shipping_address');
                if ($giftAddressId == $addressId) {
                    return true;
                }
            }
            return parent::isAddressIdApplicable($addressId);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
            return parent::isAddressIdApplicable($addressId);
        }
    }
}
