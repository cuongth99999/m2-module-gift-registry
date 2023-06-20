<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 16:21
 */

namespace Magenest\GiftRegistry\Observer\Order;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\ResourceModel\Tran;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Observer\Order
 */
class Item implements ObserverInterface
{
    /** @var ItemFactory  */
    protected $_itemFactory;

    /** @var \Magenest\GiftRegistry\Model\ResourceModel\Item  */
    protected $_itemResource;

    /** @var LoggerInterface  */
    protected $_logger;

    /** @var CustomerRepository  */
    protected $_customerRepository;

    /** @var QuoteFactory  */
    protected $_quoteFactory;

    /** @var StoreManagerInterface  */
    protected $_storeManagerInterface;

    /** @var ProductRepositoryInterface  */
    protected $_productRepository;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * Item constructor.
     *
     * @param ItemFactory $itemFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item $itemResource
     * @param CustomerRepository $customerRepository
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param ProductRepositoryInterface $productRepository
     * @param Json $json
     */
    public function __construct(
        ItemFactory $itemFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Item $itemResource,
        CustomerRepository $customerRepository,
        LoggerInterface $logger,
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManagerInterface,
        ProductRepositoryInterface $productRepository,
        Json $json
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->_logger = $logger;
        $this->_quoteFactory = $quoteFactory;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_productRepository = $productRepository;
        $this->_json = $json;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order\Item $orderItem */
        $orderItem = $observer->getEvent()->getItem();
        /** @var Order $order */
        $order = $orderItem->getOrder();
        $orderId = $order->getId();
        $productId = $orderItem->getProductId();
        $product = $this->_productRepository->getById($productId);
        if ($product) {
            $productType = $product->getTypeId();
            $request = $orderItem->getProductOptionByCode('info_buyRequest');
            if (isset($request['registry']) && $request['registry']) {
                $registryId = $request['registry'];
                $item = $request['item'];
                $qty = $orderItem->getQtyOrdered();

                if ($productType != 'configurable') {
                    //update the received quantity and send email to gift registry owner
                    $itemModel = $this->_itemFactory->create();
                    $this->_itemResource->load($itemModel, $item);
                    $note = $itemModel->getNote();
                    $orderIds = [];
                    if ($note != null) {
                        $orderIds = $this->_json->unserialize($note);
                    }
                    if (empty($orderIds) || !in_array($orderId, $orderIds)) {
                        $receivedQty = $itemModel->getData('received_qty');
                        $orderIds[] = $orderId;
                        $arr = [
                            'received_qty' => $receivedQty + $qty,
                            'note' => $this->_json->serialize($orderIds)
                        ];
                        $itemModel->addData($arr);
                        $this->_itemResource->save($itemModel);
                    }
                }
            }
        }
    }
}
