<?php
/**
 * Created by Magenest.
 * Date: 4/23/18
 * Time: 12:38
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\View\Result\Layout;

/**
 * Class AddToCart
 * @package Magenest\GiftRegistry\Controller\Index
 */
class AddToCart extends AbstractAction
{
    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Item
     */
    protected $_itemResource;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Product
     */
    protected $_productResource;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * AddToCart constructor.
     * @param Context $context
     * @param Data $data
     * @param ItemFactory $itemFactory
     * @param Item $itemResource
     * @param CurrentCustomer $currentCustomer
     * @param ProductFactory $productFactory
     * @param Product $productResource
     * @param Attribute $eavAttribute
     * @param StockRegistryInterface $stockRegistry
     * @param Serialize $serialize
     */
    public function __construct(
        Context $context,
        Data $data,
        ItemFactory $itemFactory,
        Item $itemResource,
        CurrentCustomer $currentCustomer,
        ProductFactory $productFactory,
        Product $productResource,
        Attribute $eavAttribute,
        StockRegistryInterface $stockRegistry,
        Serialize $serialize
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->currentCustomer = $currentCustomer;
        $this->_productFactory = $productFactory;
        $this->_productResource = $productResource;
        $this->eavAttribute = $eavAttribute;
        $this->stockRegistry = $stockRegistry;
        $this->serialize = $serialize;
        return parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $item = $this->_itemFactory->create();
        $this->_itemResource->load($item, $params['id']);
        $product = $this->_productFactory->create();
        $this->_productResource->load($product, $params['productId']);
        $stockItem = $this->getStockItem($item, $product);
        $hasAddress = $this->hasAddress();
        $test = [
            'stockItem' => $stockItem,
            'hasAddress' => $hasAddress
        ];
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($test);
        return $resultJson;
    }

    /**
     * @param $item
     * @param $_product
     * @return int|null
     */
    public function getStockItem($item, $_product)
    {
        $productStockObj = null;
        if ($_product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = $this->serialize->unserialize($request);
            $products = $_product->getTypeInstance()->getUsedProducts($_product);
            $options =[];
            foreach ($request['super_attribute'] as $key => $value) {
                $options[$key] = $value;
            }
            $attributes=[];
            foreach ($options as $key =>$value) {
                $eavAttribute = $this->eavAttribute->load($key);
                $attributes[$eavAttribute->getAttributeCode()] = $value;
            }

            foreach ($products as $product) {
                $check = true;
                $data =$product->getData();
                foreach ($attributes as $key => $attribute) {
                    if (!isset($data[$key]) || $data[$key]!==$attribute) {
                        $check = false;
                        continue;
                    }
                }
                if ($check==true) {
                    $id = $data['entity_id'];
                    $productStockObj = (int)$this->stockRegistry->getStockItem($id)->getData('qty');
                }
            }
        } else {
            $productStockObj = (int)$this->stockRegistry->getStockItem($_product->getId())->getData('qty');
        }
        return  $productStockObj;
    }

    /**
     * @return int
     */
    public function hasAddress()
    {
        if ($this->currentCustomer->getCustomerId() == '') {
            return -1;
        }
        $customerAddress = $this->currentCustomer->getCustomer()->getAddresses();
        if (count($customerAddress) >= 1) {
            return 1;
        }
        return 0;
    }
}
