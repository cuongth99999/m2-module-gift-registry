<?php

namespace Magenest\GiftRegistry\Observer\Order;

use Magenest\GiftRegistry\Model\ResourceModel\Tran;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Order
 * @package Magenest\GiftRegistry\Observer\Order
 */
class Order implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranFactory
     */
    protected $_orderRegistryFactory;

    /**
     * @var Tran
     */
    protected $_orderRegistryResources;

    /**
     * @var Tran\CollectionFactory
     */
    protected $_orderRegistryCollection;

    /**
     * Order constructor.
     * @param LoggerInterface $logger
     * @param TranFactory $orderRegistryFactory
     * @param Tran $orderRegistryResources
     * @param Tran\CollectionFactory $orderRegistryCollection
     */
    public function __construct(
        LoggerInterface $logger,
        TranFactory $orderRegistryFactory,
        Tran $orderRegistryResources,
        Tran\CollectionFactory $orderRegistryCollection
    ) {
        $this->logger = $logger;
        $this->_orderRegistryFactory = $orderRegistryFactory;
        $this->_orderRegistryResources = $orderRegistryResources;
        $this->_orderRegistryCollection = $orderRegistryCollection;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $item = $order->getItems();
        if (!empty($item)) {
            $orderId = $order->getId();
            $quoteId = $order->getQuoteId();
            //save it in the gift registry order table for ease of managements
            $orderRegistryCollection = $this->_orderRegistryCollection->create()->addFieldToFilter('quote_id', $quoteId);
            $orderIdList = array_column($orderRegistryCollection->getData(), 'order_id');
            if (count($orderIdList) == 1 && array_values($orderIdList)[0] == null) {
                $orderRegistryModel = $orderRegistryCollection->getFirstItem();
            } elseif (array_search($orderId, $orderIdList) !== false) {
                $orderRegistryModel = $orderRegistryCollection->getItemByColumnValue('order_id', $orderId);
            } else {
                $orderRegistryModel = $this->_orderRegistryFactory->create();
            }
            $item = reset($item);
            $giftRegistryOption = isset($item) ? $item->getProductOptionByCode('info_buyRequest') : [];
            $giftRegistryId = array_key_exists('registry', $giftRegistryOption) ? $giftRegistryOption['registry'] : $orderRegistryModel->getGiftregistryId();
            if ($giftRegistryId) {
                $status = $order->getStatus();
                $incognito = $orderRegistryModel->getData('incognito');
                $orderGiftRegistryData = [
                    'order_id' => $orderId,
                    'status' => $status,
                    'giftregistry_id' => $giftRegistryId,
                    'quote_id' => $quoteId,
                    'is_notification' => 1,
                    'incognito' => $incognito
                ];
                $orderRegistryModel->addData($orderGiftRegistryData);
                $this->_orderRegistryResources->save($orderRegistryModel);
            }
        }
    }
}
