<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:39
 */

namespace Magenest\GiftRegistry\Block\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\AddressFactory;
use Magenest\GiftRegistry\Model\Config\Priority;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\GuestsFactory;
use Magenest\GiftRegistry\Model\Item;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Collection;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TranFactory;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResources;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Size;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Store\Model\StoreManager;

/**
 * Class ManageRegistry
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ManageRegistry extends AbstractProduct implements IdentityInterface
{
    /** @var TypeFactory  */
    protected $_typeFactory;

    /** @var Type  */
    protected $_typeResource;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var FormKey
     */
    protected $_formKey;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Priority
     */
    protected $priority;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var GiftRegistry
     */
    protected $_registryResource;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var TranFactory
     */
    protected $_tranFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ProductResources
     */
    protected $_productResources;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryModel;

    /**
     * @var Size
     */
    protected $_fileSize;

    /**
     * @var GuestsFactory
     */
    protected $_guestsFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ProductResources\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var ScopeConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var Order
     */
    protected $_orderResources;

    /**
     * @var Customer
     */
    protected $_customerResources;

    /**
     * ManageRegistry constructor.
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param Image $imageModel
     * @param TranFactory $tranFactory
     * @param OrderFactory $orderFactory
     * @param RegistrantFactory $registrantFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param GiftRegistryFactory $registryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param Context $context
     * @param FormKey $formKey
     * @param CurrentCustomer $currentCustomer
     * @param Data $helper
     * @param CollectionFactory $itemFactory
     * @param Priority $priority
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param Configurable $catalogProductTypeConfigurable
     * @param Attribute $eavAttribute
     * @param StoreManager $storeManager
     * @param Size $fileSize
     * @param GuestsFactory $guestsFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
     * @param ProductResources\CollectionFactory $productCollectionFactory
     * @param Serialize $serialize
     * @param ProductResources $productResources
     * @param Json $json
     * @param Order $orderResources
     * @param Customer $_customerResources
     * @param array $data
     */
    public function __construct(
        TypeFactory $typeFactory,
        Type $typeResource,
        Image $imageModel,
        TranFactory $tranFactory,
        OrderFactory $orderFactory,
        RegistrantFactory $registrantFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        GiftRegistryFactory $registryFactory,
        GiftRegistry $giftRegistryResource,
        Context $context,
        FormKey $formKey,
        CurrentCustomer $currentCustomer,
        Data $helper,
        CollectionFactory $itemFactory,
        Priority $priority,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepositoryInterface,
        Configurable $catalogProductTypeConfigurable,
        Attribute $eavAttribute,
        StoreManager $storeManager,
        Size $fileSize,
        GuestsFactory $guestsFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
        ProductResources\CollectionFactory $productCollectionFactory,
        Serialize $serialize,
        ProductResources $productResources,
        Json $json,
        Order $orderResources,
        Customer $_customerResources,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->imageModel = $imageModel;
        $this->productRepository = $productRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->_tranFactory = $tranFactory;
        $this->_orderFactory = $orderFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_registryFactory = $registryFactory;
        $this->_registryResource = $giftRegistryResource;
        parent::__construct($context, $data);
        $this->priority = $priority;
        $this->currentCustomer = $currentCustomer;
        $this->_itemFactory = $itemFactory;
        $this->_formKey = $formKey;
        $this->helper = $helper;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->eavAttribute = $eavAttribute;
        $this->_storeConfig = $this->_scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_fileSize = $fileSize;
        $this->_guestsFactory = $guestsFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->serialize = $serialize;
        $this->_productResources= $productResources;
        $this->_json = $json;
        $this->_orderResources = $orderResources;
        $this->_customerResources = $_customerResources;
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
     * @throws NoSuchEntityException
     */
    public function getBaseUrlEvent()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event)
    {
        $giftregistryModel = $this->getRegistry();
        $giftId = $giftregistryModel->getId();
        $type = $giftregistryModel->getData('type');
        return $this->getUrl('gift_registry') . 'view/' . $type . '.html?id=' . $giftId;
    }

    /**
     * @return mixed
     */
    public function getRegistryId()
    {
        return $this->getRegistry()->getId();
    }

    /**
     * @return int
     */
    public function haveOrder()
    {
        $giftId = $this->getRegistry()->getId();
        $orderCollection = $this->_tranFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $giftId);
        if (count($orderCollection) == 0) {
            return 0;
        }
        return 1;
    }

    /**
     * @return mixed
     */
    public function getOrderCollection()
    {
        $giftId = $this->getRegistry()->getId();
        $orderCollection = $this->_tranFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $giftId);
        return $orderCollection;
    }

    /**
     * @return AbstractDb|AbstractCollection|null
     */
    public function getGuestCollection()
    {
        $giftId = $this->getRegistry()->getId();
        $orderCollection = $this->_guestsFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $giftId);
        return $orderCollection;
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public function getOrder($orderID)
    {
        $order = $this->_orderFactory->create();
        $this->_orderResources->load($order, $orderID);
        return $order;
    }

    /**
     * @param $customerEmail
     * @return array|mixed|null
     * @throws NoSuchEntityException
     */
    public function getCustomer($customerEmail)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $storeId = $this->_storeManager->getStore()->getId();
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($websiteId);
        try {
            $customer->loadByEmail($customerEmail);// load customer by email address
            $data = $customer->getData();
            return $data;
        } catch (\Exception $e) {
            $customerExists = false;
        }
    }

    /**
     * @return string
     */
    public function getUpdateActionUrl()
    {
        return $this->getUrl('gift_registry/customer/item');
    }

    public function getAddProduct()
    {
        return $this->getUrl('gift_registry/index/add');
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
     * @return mixed
     * Get event type
     */
    public function getEventType()
    {
        $type = $this->getRegistry()->getData('type');
        return $type;
    }

    /**
     * @return \Magenest\GiftRegistry\Model\Type
     */
    public function getEventTypeModel()
    {
        $type = $this->getEventType();
        $typeModel = $this->_typeFactory->create();
        $this->_typeResource->load($typeModel, $type, 'event_type');
        return $typeModel;
    }

    /**
     * @return Collection
     */
    public function getItemList()
    {
        $giftId = $this->getRegistry()->getId();
        return $this->_itemFactory->create()->addFieldToFilter('gift_id', $giftId);
    }

    /**
     * @return \Magenest\GiftRegistry\Model\GiftRegistry
     */
    public function getRegistry()
    {
        if ($this->_giftregistryModel == null) {
            $params = $this->getRequest()->getParams();
            $giftregistryModel = $this->_registryFactory->create();
            if ($params['id']) {
                $this->_registryResource->load($giftregistryModel, $params['id']);
            }
            $this->_giftregistryModel = $giftregistryModel;
        }

        return $this->_giftregistryModel;
    }

    /**
     * @return AbstractCollection
     * Get shipping address of customer.
     */
    public function getShippingAddress()
    {
        return $this->_addressFactory->create()->getCollection();
    }

    /**
     * get the customer address
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerAddress()
    {
        $addressArr = [];
        $customerId = $this->currentCustomer->getCustomerId();//->getAddressesCollection();

        $customer = $this->_customerFactory->create();
        $this->_customerResources->load($customer, $customerId);

        $addressCollection = $customer->getAddressesCollection();

        if ($addressCollection->getSize()) {
            /**
             * @var  $address Address
             */
            foreach ($addressCollection as $address) {
                $addressArr[] = ['name' => $address->getName() . ' ' . $address->getStreetFull() . ' ' . $address->getRegion() . ' ' . $address->getCountry(),
                    'id' => $address->getId()
                ];
            }
        }

        return $addressArr;
    }

    /**
     * @return mixed
     * Get registrant of registry.
     */
    public function getRegistrants()
    {
        $registryId = $this->getRegistry()->getId();
        if ($registryId > 0) {
            $registrants = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', $registryId);
        } else {
            $registrants = $this->_registrantFactory->create()->getCollection()->addFieldToFilter('giftregistry_id', -1);
        }
        return $registrants;
    }

    /**
     * @return string
     * Get save url
     */
    public function getSaveAddressUrl()
    {
        $registryId = $this->getRegistry()->getId();
        return $this->getUrl('gift_registry/customer/post', ['event_id' => $registryId]);
    }

    /**
     * @return string
     * Get guest view url
     */
    public function getLinkShare()
    {
        $registryModel = $this->getRegistry();
        $type = $registryModel->getData('type');
        $registryId = $registryModel->getId();
        $linkShare = $this->getUrl('gift-registry') . 'view/gift/' . $registryId . '/' . $type . '.html';
        return $linkShare;
    }

    /**
     * @return string
     */
    public function getGiftTitle()
    {
        $id = $this->getRegistry()->getId();
        $gift = $this->_registryFactory->create();
        $this->_registryResource->load($gift, $id, 'gift_id');
        return $gift ? $gift->getTitle() : "";
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $gift = $this->getRegistry();
        return $gift ? $gift->getDescription() : "";
    }

    /**
     * @return string
     */
    public function getSendMailUrl()
    {
        return $this->getUrl('gift_registry/guest/checkemail');
    }

    /**
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('gift_registry/customer/preview');
    }

    /**
     * Ajax delete event
     *
     * @return string
     */
    public function deleteEvent()
    {
        return $this->getUrl('gift_registry/customer/delete');
    }

    /**
     * @return string
     */
    public function getListGiftUrl()
    {
        return $this->getUrl() . 'gift_registry/customer/mygiftregistry/';
    }

    /**
     * @param $type
     * @return string
     */
    public function getImageAvatarbyType($type)
    {
        $typeModel = $this->getEventTypeModel();
        $path = $typeModel->getData('thumnail');
        $url = "";
        if ($path != "") {
            $url = $this->imageModel->getBaseUrl() . $path;
        }
        return $url;
    }

    /**
     * @param $path
     * @return string
     */
    public function getImageGRUrl($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getCustomizableOption(Item $item)
    {
        $product = $item->getProduct();
        $options = $product->getOptions();
        $responseData = [];
        $customOptions = $product->getCustomOptions();
        if (isset($customOptions['option_ids'])) {
            $optionIds = explode(',', $product->getCustomOptions()['option_ids']->getData('value'));
            $optionTypeIds = [];
            foreach ($optionIds as $id) {
                array_push($optionTypeIds, $product->getCustomOptions()['option_' . $id]->getData('value'));
            }
            $i = 0;
            foreach ($options as $option) {
                $optionData = $option->getValues();
                foreach ($optionData as $data) {
                    if (in_array($data->getData('option_type_id'), $optionTypeIds)) {
                        array_push($responseData, ['label' => $product->getData('options')[$i++]->getData('title'), 'value' => $data->getData('title')]);
                        break;
                    }
                }
            }
        }
        return $responseData;
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductImage($product)
    {
        if ($product->getTypeId() == 'configurable') {
            $simpleId = $product->getCustomOptions()['simple_product']->getProductId();
            if ($simpleId) {
                $product = $this->_productFactory->create();
                $this->_productResources->load($product, $simpleId);
            }
        }
        return $this->_imageHelper
            ->init($product, 'category_page_list')
            ->keepAspectRatio(true)
            ->getUrl();
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        return parent::getProductPrice($product); // TODO: Change the autogenerated stub
    }

    /**
     * @param $item
     * @param $product
     * @return float|null
     */
    public function getPrice($item, $product)
    {
        $price = null;
        if ($product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = $this->serialize->unserialize($request);
            $products = $product->getTypeInstance()->getUsedProducts($product);
            $options = [];
            foreach ($request['super_attribute'] as $key => $value) {
                $options[$key] = $value;
            }
            $attributes = [];
            foreach ($options as $key => $value) {
                $eavAttribute = $this->eavAttribute->load($key);
                $attributes[$eavAttribute->getAttributeCode()] = $value;
            }

            foreach ($products as $product) {
                $check = true;
                $data = $product->getData();
                foreach ($attributes as $key => $attribute) {
                    if (!isset($data[$key]) || $data[$key] !== $attribute) {
                        $check = false;
                        continue;
                    }
                }
                if ($check == true) {
                    $id = $data['entity_id'];
                    $currentProduct = $this->_productFactory->create();
                    $this->_productResources->load($currentProduct, $id);
                    if ($currentProduct) {
                        $price = $currentProduct->getFinalPrice();
                    }
                }
            }
        } else {
            $price = $product->getFinalPrice();
        }
        return $price;
    }

    /**
     * @param $product
     * @param $imageId
     * @param null $item
     * @param array $attributes
     * @return string
     */
    public function getCustomImageUrl($product, $imageId, $item = null, $attributes = [])
    {
        try {
            if ($product->getTypeId() != 'configurable') {
                return parent::getImage($product, $imageId, $attributes)->getImageUrl();
            } else {
                if ($product->getImage() && $product->getImage() != 'no_selection') {
                    $image = $this->getUsedImageChildProduct($item, $product);
                    return $this->getUrl('pub/media/catalog') . 'product' . $image;
                }
            }
        } catch (\Exception $exception) {
        }
        return '';
    }

    /**
     * @return bool
     */
    public function getIsExpiredGift()
    {
        $registryModel = $this->getRegistry();
        if ($registryModel->getData('is_expired') == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $item
     * @param $product
     * @return string
     */
    public function getUsedImageChildProduct($item, $product)
    {
        $request = $item->getData('buy_request');
        $request = $this->serialize->unserialize($request);
        $products = $product->getTypeInstance()->getUsedProducts($product);
        $options = [];
        foreach ($request['super_attribute'] as $key => $value) {
            $options[$key] = $value;
        }
        $attributes = [];
        foreach ($options as $key => $value) {
            $eavAttribute = $this->eavAttribute->load($key);
            $attributes[$eavAttribute->getAttributeCode()] = $value;
        }

        foreach ($products as $product) {
            $check = true;
            $data = $product->getData();
            foreach ($attributes as $key => $attribute) {
                if (!isset($data[$key]) || $data[$key] !== $attribute) {
                    $check = false;
                    continue;
                }
            }
            if ($check == true) {
                $id = $data['entity_id'];
                $currentProduct = $this->_productFactory->create();
                $this->_productResources->load($currentProduct, $id);
                if ($currentProduct) {
                    $image = $currentProduct->getImage();
                }
            }
        }
        return $image;
    }

    /**
     * @return bool
     */
    public function isShowAboutEvent()
    {
        $typeModel = $this->getEventTypeModel();
        if ($typeModel->getId() && $typeModel->getData('fields') != '') {
            $fields = $this->_json->unserialize($typeModel->getData('fields'));
            if (in_array('about_event', $fields)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $type
     * @param $gift_option
     * @return array|null
     */
    public function convertGiftOption($type, $gift_option)
    {
        if ($gift_option == null || $gift_option == '[]') {
            return null;
        }

        $gift_option_arr = $gift_option;
        $result = [];
        if ($type == 'birthdaygift') {
            foreach ($gift_option_arr as $key => $option) {
                if ($key == 'date-birthday') {
                    $result['date'][0] = $option;
                }
                if ($key == 'name-birthday') {
                    $result['field'][1] = $option;
                }
            }
        } elseif ($type == 'christmasgift') {
            foreach ($gift_option_arr as $key => $option) {
                if ($key == 'greeting') {
                    $result['area'][0] = $option;
                }
            }
        } elseif ($type == 'weddinggift') {
            foreach ($gift_option_arr as $key => $option) {
                if ($key == 'husband_name') {
                    $result['field'][0] = $option;
                }
                if ($key == 'wife_name') {
                    $result['field'][1] = $option;
                }
            }
        } elseif ($type == 'babygift') {
            foreach ($gift_option_arr as $key => $option) {
                if ($key == 'baby_name') {
                    $result['field'][0] = $option;
                }
            }
        }
        if (empty($result)) {
            $result = $gift_option;
        }
        return $result;
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getGoogleApi()
    {
        return $this->helper->getGoogleApi();
    }

    /**
     * @param $gift_id
     * @return int
     */
    public function getTotalDesiredGifts($gift_id)
    {
        $itemCollections = $this->_itemFactory->create()->addFieldToFilter('gift_id', $gift_id);
        $total = 0;
        foreach ($itemCollections as $itemCollection) {
            $total += $itemCollection->getData('qty');
        }
        return $total;
    }

    /**
     * @param $gift_id
     * @return int
     */
    public function getTotalReceivedGifts($gift_id)
    {
        $itemCollections = $this->_itemFactory->create()->addFieldToFilter('gift_id', $gift_id);
        $total = 0;
        foreach ($itemCollections as $itemCollection) {
            $total += $itemCollection->getData('received_qty');
        }
        return $total;
    }

    /**
     * @return mixed|string
     */
    public function getCurrentUrl()
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $arr = explode('?', $currentUrl);
        if (is_array($arr)) {
            $currentUrl = $arr[0];
        }
        return $currentUrl;
    }

    /**
     * @return false|mixed|string
     */
    public function getTabEnable()
    {
        $url = $this->_urlBuilder->getCurrentUrl();
        if (strpos($url, 'gift-registry/manage') !== false) {
            $arr = explode('gift-registry/manage/', $url);
            if (isset($arr[1])) {
                $params = explode('/', $arr[1]);
                return $params[0];
            }
        }
        return false;
    }

    /**
     * @return float
     */
    public function getMaxUploadSize()
    {
        return $this->_fileSize->getMaxFileSizeInMb();
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getAvailableCountries()
    {
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToSelect('*');

        return $collection;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getProduct()->getIdentities();
    }

    /**
     * @param $type
     * @return string
     */
    public function getImageBannerDefault($type)
    {
        $typeModel = $this->getEventTypeModel();
        $path = $typeModel->getData('image');
        $url = "";
        if ($path != "") {
            $url = $this->imageModel->getBaseUrl() . $path;
        }
        return $url;
    }

    /**
     * @param $registry
     * @param $imageDefault
     * @throws AlreadyExistsException|LocalizedException
     */
    public function updateData($registry, $imageDefault)
    {
        $arr = explode($this->imageModel->getBaseUrl(), $imageDefault);
        if (count($arr)) {
            $image = $arr[1];
        } else {
            throw new LocalizedException(__('Error creating Gift Registry'));
        }
        if (isset($image)) {
            $registry->setImage($image);
            $this->_registryResource->save($registry);
        }
    }

    /**
     * @param $params
     * @return bool|string
     */
    public function jsonEncode($params)
    {
        return $this->_json->serialize($params);
    }

    /**
     * @param $params
     * @return array|bool|float|int|mixed|string|null
     */
    public function jsonDecode($params)
    {
        return $this->_json->unserialize($params);
    }
}
