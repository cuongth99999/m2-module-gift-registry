<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */

namespace Magenest\GiftRegistry\Block\Customer\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\Config\Priority;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResources;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class ViewRegistry
 * @package Magenest\GiftRegistry\Block\Customer\Registry
 */
class ViewRegistry extends AbstractProduct
{
    protected $_giftregistryModel = null;

    /** @var CurrentCustomer $currentCustomer */
    protected $currentCustomer;

    /** @var CollectionFactory $_itemFactory */
    protected $_itemFactory;

    /** @var FormKey $_formKey */
    protected $_formKey;

    /** @var Data $helper */
    protected $helper;

    /** @var Priority $priority */
    protected $priority;

    /** @var TypeFactory $_typeFactory */
    protected $_typeFactory;

    /** @var ProductFactory $_productFactory */
    protected $_productFactory;

    /** @var ProductRepositoryInterface $productRepository */
    protected $productRepository;

    /** @var RegistrantFactory $registrantFactory */
    protected $registrantFactory;

    /** @var Attribute $eavAttribute */
    protected $eavAttribute;

    /** @var Configurable $configurableType */
    protected $configurableType;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryFactory;

    /**
     * @var GiftRegistry
     */
    protected $_giftregistryResource;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var Type
     */
    protected $_resourceType;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var ProductResources
     */
    protected $_productResources;

    /**
     * @var Json
     */
    protected $json;

    /**
     * ViewRegistry constructor.
     * @param RegistrantFactory $registrantFactory
     * @param Context $context
     * @param FormKey $formKey
     * @param CurrentCustomer $currentCustomer
     * @param Data $helper
     * @param CollectionFactory $itemFactory
     * @param Priority $priority
     * @param TypeFactory $typeFactory
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param Attribute $eavAttribute
     * @param Configurable $configurable
     * @param Image $imageModel
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param StockRegistryInterface $stockRegistry
     * @param Serialize $serialize
     * @param Type $resourceType
     * @param CountryFactory $countryFactory
     * @param ProductResources $productResources
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        RegistrantFactory $registrantFactory,
        Context $context,
        FormKey $formKey,
        CurrentCustomer $currentCustomer,
        Data $helper,
        CollectionFactory $itemFactory,
        Priority $priority,
        TypeFactory $typeFactory,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepositoryInterface,
        Attribute $eavAttribute,
        Configurable $configurable,
        Image $imageModel,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResource,
        StockRegistryInterface $stockRegistry,
        Serialize $serialize,
        Type $resourceType,
        CountryFactory $countryFactory,
        ProductResources $productResources,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registrantFactory = $registrantFactory;
        $this->productRepository = $productRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
        $this->priority = $priority;
        $this->currentCustomer = $currentCustomer;
        $this->_itemFactory = $itemFactory;
        $this->_formKey = $formKey;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        $this->imageModel = $imageModel;
        $this->configurableType = $configurable;
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_giftregistryResource = $giftRegistryResource;
        $this->stockRegistry = $stockRegistry;
        $this->serialize = $serialize;
        $this->_resourceType = $resourceType;
        $this->_countryFactory = $countryFactory;
        $this->_productResources = $productResources;
        $this->json = $json;
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getIdEvent()
    {
        $params = $this->getRequest()->getParams();

        $giftRegistryId =(isset($params['id'])) ? $params['id'] : $params['event_id'];
        return $this->_itemFactory->create()
            ->addFieldToFilter('gift_id', $giftRegistryId);
    }

    /**
     * @return string
     */
    public function getUpdateActionUrl()
    {
        return $this->getUrl('gift_registry/customer/item');
    }

    /**
     * @param $item_id
     * @param $id
     * @return string
     */
    public function getDeleteItemUrl($item_id, $id)
    {
        return $this->getUrl('gift_registry/customer/item', ['type'=>'delete' , 'item_id' =>$item_id ,'id' => $id]);
    }

    /**
     * @param $item
     * @return array
     */
    public function getItemOptions($item)
    {
        return $this->helper->getOptions($item);
    }

    /**
     * @return string
     */
    public function getEditAddress()
    {
        return $this->getUrl('customer/address/edit');
    }

    /**
     * @param $options
     * @param $name
     * @param $itemID
     *
     * @return ProductInterface|DataObject
     * @throws NoSuchEntityException
     */
    public function getProductByOption($options, $name, $itemID)
    {
        $products = $this->_productFactory->create()->getCollection();
        $products = $products->addFieldToFilter('name', ['like' => '%' . $name . '%']);
        foreach ($options as $option) {
            $products = $products->addFieldToFilter('name', ['like' => '%' . $option['value'] . '%']);
        }
        $product = $products->getFirstItem();
        if ($product->getData('entity_id')) {
            $result = $this->productRepository->getById($product->getData('entity_id'));
            $customOptionsArr = $this->helper->getCustomOptionAsArr($itemID);
            $result->setCustomOptions($customOptionsArr);
            return $result;
        } else {
            return $product;
        }
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
     * @return DataObject
     */
    public function getRegistrant()
    {
        $params = $this->getRequest()->getParams();
        $giftRegistryId =(isset($params['id'])) ? $params['id'] : $params['event_id'];
        $registrant = $this->registrantFactory->create()->getCollection()->addFieldToFilter("giftregistry_id", $giftRegistryId)->getFirstItem();
        return $registrant;
    }

    /**
     * @param $id
     * @return string
     */
    public function getCountryNameById($id)
    {
        $country = $this->_countryFactory->create()->loadByCode($id);
        return $country->getName();
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function jsonEncode($data)
    {
        return $this->json->serialize($data);
    }

    /**
     * @param $string
     * @return array|bool|float|int|mixed|string|null
     */
    public function jsonDecode($string)
    {
        return $this->json->unserialize($string);
    }

}
