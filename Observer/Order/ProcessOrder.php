<?php

namespace Magenest\GiftRegistry\Observer\Order;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\ResourceModel\Tran;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProcessOrder
 * @package Magenest\GiftRegistry\Observer\Order
 */
class ProcessOrder implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var Order $order
     */
    protected $order;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var Registrant
     */
    protected $_registrantResource;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var GiftRegistry
     */
    protected $_giftRegistryResource;

    /**
     * @var TranFactory
     */
    protected $_orderRegistryFactory;

    /**
     * @var Tran
     */
    protected $_orderRegistryResource;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ProcessOrder constructor.
     * @param Order $order
     * @param RegistrantFactory $registrantFactory
     * @param Registrant $registrantResource
     * @param GiftRegistryFactory $giftRegistry
     * @param GiftRegistry $giftRegistryResource
     * @param TranFactory $transactionFactory
     * @param Tran $orderRegistryResource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Order $order,
        RegistrantFactory $registrantFactory,
        Registrant $registrantResource,
        GiftRegistryFactory $giftRegistry,
        GiftRegistry $giftRegistryResource,
        TranFactory $transactionFactory,
        Tran $orderRegistryResource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->order = $order;
        $this->_registrantFactory = $registrantFactory;
        $this->_registrantResource = $registrantResource;
        $this->_giftRegistryFactory = $giftRegistry;
        $this->_giftRegistryResource = $giftRegistryResource;
        $this->_orderRegistryFactory = $transactionFactory;
        $this->_orderRegistryResource = $orderRegistryResource;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            /**
             * @var OrderInterface $order
             */
            $orders = $observer->getData('order') ? [$observer->getData('order')] : $observer->getData('orders');
            foreach ($orders as $order) {
                $items = $order->getItems();
                $registryId = '';
                if (count($items)) {
                    $item = reset($items);
                }
                if (isset($item) && $item->getData('product_options')) {
                    $infoBuyRequest = $item->getData('product_options')['info_buyRequest'];
                }
                if (isset($infoBuyRequest['registry'])) {
                    $registryId = $infoBuyRequest['registry'];
                }
                if ($registryId != '') {
                    $registrant = $this->_registrantFactory->create();
                    $this->_registrantResource->load($registrant, $registryId, 'giftregistry_id');
                    $quoteId = $order->getQuoteId();
                    $registry = $this->_giftRegistryFactory->create();
                    $this->_giftRegistryResource->load($registry, $registryId);
                    $urlSite = $this->_storeManager->getStore()->getBaseUrl();
                    $guestEmail = $order->getCustomerEmail();
                    $billingData = $order->getBillingAddress()->getData();
                    if (isset($billingData['firstname']) && isset($billingData['lastname'])) {
                        $customerName = $billingData['firstname'] . ' ' . $billingData['lastname'];
                    } else {
                        $customerName = '';
                    }
                    $recipient_email = $registrant->getData('email');
                    $recipient_name = $registrant->getData('firstname') . ' ' . $registrant->getData('lastname');
                    $params = [
                        'giver_email' => $guestEmail,
                        'giver_name' => $customerName,
                        'url' => $urlSite,
                        'recipient_email' => $recipient_email,
                        'recipient_name' => $recipient_name
                    ];
                    $orderRegistryModel = $this->_orderRegistryFactory->create();
                    $this->_orderRegistryResource->load($orderRegistryModel, $quoteId, 'quote_id');
                    $params['message'] = $orderRegistryModel->getData('message');
                    $params['sender'] = $orderRegistryModel->getData('sender');
                    $params['incognito'] = $orderRegistryModel->getData('incognito');
                    //Gift confirmation email
                    $registry->sendEmail($order, null, $registrant);
                    //Email notifying owner of registry gift
                    $registry->sendEmail($order, $params, null);
                } else {
                    throw new LocalizedException(__('An Error Has Occurred When Ordering'));
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
