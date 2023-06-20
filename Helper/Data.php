<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:40
 */

namespace Magenest\GiftRegistry\Helper;

use Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\GiftRegistryTmpFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory as TypeCollection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResources;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Magenest\GiftRegistry\Helper
 */
class Data extends AbstractHelper
{
    const BACKGROUND_GIFTREGISTRY_SEARCH = 'giftregistry/setting/banner_default';
    const GIFTREGISTRY_DESIRED_QUANTITY = 'giftregistry/setting/desired_quantity';
    const GIFTREGISTRY_ENABLE_MODULE = 'giftregistry/general/enable';
    const GIFTREGISTRY_GOOGLE_API = 'giftregistry/setting/map_api';
    const GIFTREGISTRY_HIDE_EMPTY_EVENT_TYPE = 'giftregistry/general/hide_empty_event_type';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var GiftRegistryProviderInterface
     */
    protected $_giftRegistryProvider;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var Configuration
     */
    protected $configurationHelper;

    /**
     * @var PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var null
     */
    protected $_giftregistryModel = null;

    /**
     * @var GiftRegistry
     */
    protected $_giftregistryResource;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceNumber;

    /**
     * @var GiftRegistryTmpFactory
     */
    protected $_giftregistryTmpFactory;

    /**
     * @var GiftRegistryTmp
     */
    protected $_giftregistryTmpResource;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var SortOrder
     */
    protected $sortOrder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_search;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @var ProductResources
     */
    protected $_productResources;
    /**
     * @var ProductResources\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var TypeCollection
     */
    protected $_typeCollection;
    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * Data constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param ItemFactory $itemFactory
     * @param OptionFactory $optionFactory
     * @param Configuration $configuration
     * @param StoreManagerInterface $storeManager
     * @param PostHelper $postDataHelper
     * @param GiftRegistryProviderInterface $giftRegistryProviderInterface
     * @param ProductRepositoryInterface $productRepository
     * @param Encryptor $encryptor
     * @param CollectionFactory $itemFactoryData
     * @param GiftRegistry $giftRegistryResource
     * @param ProductFactory $productFactory
     * @param Image $imageHelper
     * @param Attribute $eavAttribute
     * @param \Magento\Framework\Pricing\Helper\Data $priceNumber
     * @param GiftRegistryTmpFactory $giftRegistryTmpFactory
     * @param GiftRegistryTmp $giftRegistryTmpResource
     * @param Serialize $serialize
     * @param SearchCriteria $searchCriteria
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrder $sortOrder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductResources\CollectionFactory $productCollectionFactory
     * @param Configurable $configurable
     * @param ProductResources $productResources
     * @param TypeCollection $typeCollection
     * @param Repository $assetRepo
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        GiftRegistryFactory $giftRegistryFactory,
        ItemFactory $itemFactory,
        OptionFactory $optionFactory,
        Configuration $configuration,
        StoreManagerInterface $storeManager,
        PostHelper $postDataHelper,
        GiftRegistryProviderInterface $giftRegistryProviderInterface,
        ProductRepositoryInterface $productRepository,
        Encryptor $encryptor,
        CollectionFactory $itemFactoryData,
        GiftRegistry $giftRegistryResource,
        ProductFactory $productFactory,
        Image $imageHelper,
        Attribute $eavAttribute,
        \Magento\Framework\Pricing\Helper\Data $priceNumber,
        GiftRegistryTmpFactory $giftRegistryTmpFactory,
        GiftRegistryTmp $giftRegistryTmpResource,
        Serialize $serialize,
        SearchCriteria $searchCriteria,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrder $sortOrder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductResources\CollectionFactory $productCollectionFactory,
        Configurable $configurable,
        ProductResources $productResources,
        TypeCollection $typeCollection,
        Repository $assetRepo
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->productRepository = $productRepository;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_giftRegistryProvider = $giftRegistryProviderInterface;
        $this->itemFactory = $itemFactory;
        $this->optionFactory = $optionFactory;
        $this->configurationHelper = $configuration;
        $this->encryptor = $encryptor;
        $this->_itemFactory = $itemFactoryData;
        $this->_giftregistryResource = $giftRegistryResource;
        $this->_productFactory = $productFactory;
        $this->_imageHelper = $imageHelper;
        $this->eavAttribute = $eavAttribute;
        $this->priceNumber = $priceNumber;
        $this->_giftregistryTmpFactory = $giftRegistryTmpFactory;
        $this->_giftregistryTmpResource = $giftRegistryTmpResource;
        $this->serialize = $serialize;
        $this->searchCriteria = $searchCriteria;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrder = $sortOrder;
        $this->_search= $searchCriteriaBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_configurable = $configurable;
        $this->_productResources = $productResources;
        $this->_typeCollection = $typeCollection;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isAllow()
    {
        return true;
    }

    /**
     * If customer not logged in return 0 or customer logged in and have no gift registry return 0 too
     * If customer has logged in and have 1 gift registry return 1
     * If customer has logged in and have more than 1 gift registry return 2
     *
     * @return int
     * @throws LocalizedException
     */
    public function getHaveOneRegistry()
    {
        $customer = $this->getCustomer();
        if (is_object($customer) && $customer->getId()) {
            $customerId = $customer->getId();
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);
            $size = $collection->getSize();
            if ($size == 0) {
                return 0;
            } else {
                return $size;
            }
        } else {
            return 0;
        }
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getGiftId()
    {
        $customer = $this->getCustomer();
        $giftRegistryId = 0;

        if (is_object($customer) && $customer->getId()) {
            $customerId = $customer->getId();
            $collection = $this->_giftRegistryFactory->create()->getAllGiftRegistryByCustomerId($customerId);

            if ($collection->getSize() > 0) {
                $giftRegistryId = $collection->getFirstItem()->getId();
            }
        }
        return $giftRegistryId;
    }

    /**
     * Retrieve current customer
     *
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomerDataObject();
        }
        return null;
    }

    /**
     * Check whether customer logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

    /**
     * @param $item
     * @param $giftId
     * @param array $params
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAddParams($item, $giftId, array $params = [])
    {
        $params = [];
        $params['giftregistry'] = $giftId;
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }

        if (method_exists($item, 'getProductId')) {
            $productId = $item->getProductId();
        }

        $store = $this->_storeManager->getStore();
        $url = $store->getUrl('gift_registry/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getOptionByCode($itemId, $code)
    {
        $optionArr = [];
        $optionCollection = $this->optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId)
            ->addFieldToFilter('code', $code);

        if ($optionCollection->getSize() > 0) {
            foreach ($optionCollection as $option) {
                $optionArr[$option->getCode()] = $option;
            }
        }

        return $optionArr;
    }

    /**
     * @param $item
     * @return array
     */
    public function getOptions($item)
    {
        return $this->configurationHelper->getOptions($item);
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getCustomOptionAsArr($itemId)
    {
        $optionArr = [];
        $optionCollection = $this->optionFactory->create()->getCollection()->addFieldToFilter('gift_item_id', $itemId);

        if ($optionCollection->getSize() > 0) {
            foreach ($optionCollection as $option) {
                $optionArr[$option->getCode()] = $option;
            }
        }

        return $optionArr;
    }

    /**
     * @param $customerId
     * @param $type
     * @return bool
     */
    public function isHaveUnexpiredGiftByDate($customerId, $type)
    {
        $giftRegistry = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_expired', 0)
            ->addFieldToFilter('date', ['gteq' => date('Y-m-d')]);
        if ($giftRegistry->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @throws AlreadyExistsException
     */
    public function updateExpiredGift()
    {
        $giftModel = $this->_giftRegistryFactory->create();
        $expiredGiftIds = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('date', ['lt' => date('Y-m-d')])
            ->addFieldToFilter('is_expired', 0)
            ->getAllIds();
        foreach ($expiredGiftIds as $key => $expiredGiftId) {
            $this->_giftregistryResource->load($giftModel, $expiredGiftId);
            $giftModel->setData('is_expired', 1);
            $this->_giftregistryResource->save($giftModel);
        }
    }

    /**
     * @param $items
     * @return bool
     */
    public function isQuoteContainExpiredEventItem($items)
    {
        foreach ($items as $item) {
            $buyRequest = $item->getBuyRequest()->getData();
            if (isset($buyRequest['registry'])) {
                $registryId = $buyRequest['registry'];
                $registry = $this->_giftRegistryFactory->create();
                $this->_giftregistryResource->load($registry, $registryId);
                if ($registry->getIsExpired()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getGiftRegistryByCustomer()
    {
        $customer = $this->_customerSession->getCustomer();
        $customerId = $customer->getId();
        if (is_object($customer) && $customerId) {
            $giftRegistry = $this->_giftRegistryFactory->create();
            $giftData = $giftRegistry->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_expired', 0)
                ->getData();
            return $giftData;
        }
        return [];
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function isHaveUnexpiredGift($customerId)
    {
        $giftRegistry = $this->_giftRegistryFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_expired', 1);
        if ($giftRegistry->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getBackgroundSearchGiftRegistryPage()
    {
        $store = $this->_storeManager->getStore()->getId();
        $value = $this->scopeConfig->getValue(
            self::BACKGROUND_GIFTREGISTRY_SEARCH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $value != "" ? $mediaUrl . "giftregistry/" . $value : null;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDisplayQtyMode()
    {
        $store = $this->_storeManager->getStore()->getId();
        $value = $this->scopeConfig->getValue(
            self::GIFTREGISTRY_DESIRED_QUANTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $value;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isEnableExtension()
    {
        $store = $this->_storeManager->getStore()->getId();
        $value = $this->scopeConfig->getValue(
            self::GIFTREGISTRY_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $value;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isHideEmptyEventType()
    {
        $store = $this->_storeManager->getStore()->getId();
        $value = $this->scopeConfig->getValue(
            self::GIFTREGISTRY_HIDE_EMPTY_EVENT_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $value;
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getGoogleApi()
    {
        $store = $this->_storeManager->getStore()->getId();
        $value = $this->scopeConfig->getValue(
            self::GIFTREGISTRY_GOOGLE_API,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $value != "" ? $value : 'AIzaSyAqH83QqletUOJXv14oLfl76kcEAW29vhw';
    }

    /**
     * @param $params
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGiftRegistryItem($params)
    {
        $giftregistryModel = $this->getGiftRegistry($params);
        $identifier = trim($this->_storeManager->getStore()->getCurrentUrl(), '/');
        if (isset($params['gift_id']) || strpos($identifier, 'gift-registry/view') !== true) {
            $gift_id = $giftregistryModel->getData('gift_id');
            $giftRegistryItem = $this->_itemFactory->create()->addFieldToFilter('gift_id', $gift_id);
        } else {
            $giftRegistryItem = $this->_itemFactory->create()->addFieldToFilter('gift_id', $giftregistryModel->getId());
        }
        $collections = $giftRegistryItem->setOrder('gift_item_id', 'DESC');
        $giftRegistryItems = $this->getItemData($collections);
        if (isset($params['sort'])&&isset($params['value'])) {
            if ($params['sort'] == 'default') {
                $params['sort'] = 'gift_item_id';
            } elseif ($params['sort'] == 'qty') {
                $params['sort'] = 'desired_qty';
            }
            if ($params['value'] == 'high') {
                usort($giftRegistryItems, function ($a, $b) use ($params) {
                    if ($a[$params['sort']] == $b[$params['sort']]) {
                        return 0;
                    }
                    return ($a[$params['sort']] > $b[$params['sort']]) ? -1 : 1;
                });
            } elseif ($params['value'] == 'low') {
                usort($giftRegistryItems, function ($a, $b) use ($params) {
                    if ($a[$params['sort']] == $b[$params['sort']]) {
                        return 0;
                    }
                    return ($a[$params['sort']] < $b[$params['sort']]) ? -1 : 1;
                });
            }
        }
        return $giftRegistryItems;
    }

    /**
     * @param $params
     * @return \Magenest\GiftRegistry\Model\GiftRegistry
     * @throws NoSuchEntityException
     */
    public function getGiftRegistry($params)
    {
        if ($this->_giftregistryModel == null) {
            if (isset($params['pathInfo'])) {
                $pathInfo = $params['pathInfo'];
            } else {
                $pathInfo = $this->_storeManager->getStore()->getCurrentUrl();
            }
            $identifier = trim($pathInfo, '/');
            if (strpos($identifier, 'gift-registry/view') !== false) {
                $arr = explode('gift-registry/view/', $identifier);
                if (isset($arr[1])) {
                    $gift_id = explode('/', $arr[1]);
                    $giftregistryModel = $this->_giftRegistryFactory->create();
                    $this->_giftregistryResource->load($giftregistryModel, $gift_id[1]);
                    $this->_giftregistryModel = $giftregistryModel;
                }
            } else {
                $giftregistryTmp = $this->_giftregistryTmpFactory->create();
                if (isset($params['gift_id'])) {
                    $gift_id = $params['gift_id'];
                } else {
                    $arry = explode('preview/gift_id/', $identifier);
                    $gift_id = $arry[1];
                }
                $this->_giftregistryTmpResource->load($giftregistryTmp, $gift_id);
                $this->_giftregistryModel = $giftregistryTmp;
            }
        }
        return $this->_giftregistryModel;
    }

    /**
     * @param $collection
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItemData($collection)
    {
        $results = [];
        if ($collection->getItems()) {
            $collectionGiftId = $collection->getFirstItem()->getData('gift_id');
            $itemConfig = $this->_itemFactory->create()
                ->addFieldToFilter('gift_id', $collectionGiftId)
                ->getItems();
            foreach ($collection as $item) {
                $itemData = $item->getData();
                $productId = $itemData['product_id'];
                $product = $this->_productFactory->create();
                $this->_productResources->load($product, $productId);
                $urlProduct = $product->getUrlModel()->getUrl($product);
                //getDisplayQtyMode
                $displayQtyMode = $this->getDisplayQtyMode();
                //image product
                if ($product->getTypeId() == 'configurable') {
                    $options = $this->getOptions($itemConfig[$itemData['gift_item_id']]);
                    foreach ($options as $option) {
                        $itemData[strtolower($option['label'])] = $option['value'];
                        $attribute[$option['option_id']] = (int)$option['option_value'];
                    }
                    $product = $this->_configurable->getProductByAttributes($attribute, $product);
                    $imageUrl = $this->_imageHelper
                        ->init($product, 'category_page_grid')
                        ->keepAspectRatio(true)
                        ->getUrl();
                } else {
                    $imageUrl = $this->_imageHelper
                        ->init($product, 'category_page_grid')
                        ->keepAspectRatio(true)
                        ->getUrl();
                }
                $itemData['productImage'] = $imageUrl;
                $itemData['product_name'] = html_entity_decode($itemData['product_name']);
                $price = $this->getPrice($item, $product);
                $priceNumber = $this->priceNumber->currency($price, true, false);
                $itemData['urlProduct'] = $urlProduct;
                $itemData['price'] = $priceNumber;
                $itemData['displayQtyMode'] = $displayQtyMode;
                $itemData['desired_qty'] = $itemData['qty'] - $itemData['received_qty'];
                $results[] = $itemData;
            }
        }
        return $results;
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
                    $currentProduct = $this->_productFactory->create();
                    $this->_productResources->load($currentProduct, $id);
                    if ($currentProduct) {
                        $price =$currentProduct->getFinalPrice();
                    }
                }
            }
        } else {
            $price = $product->getFinalPrice();
        }
        return $price;
    }

    /**
     * @param $textInput
     * @return ProductInterface[]
     */
    public function searchByName($textInput)
    {
        $nameFilter = $this->filterBuilder
            ->setField('name')
            ->setConditionType('like')
            ->setValue('%' . $textInput . '%')
            ->create();
        $filterGroup= $this->filterGroupBuilder->addFilter($nameFilter)->create();
        $searchCriteria = $this->_search
            ->setFilterGroups([$filterGroup])
            ->setPageSize(6)
            ->create();
        $result= $this->productRepository->getList($searchCriteria);
        return $result->getItems();
    }

    /**
     * @throws LocalizedException
     */
    public function deleteGRTmp()
    {
        $connection = $this->_giftregistryTmpResource->getConnection();
        $tableName = $this->_giftregistryTmpResource->getMainTable();
        $connection->truncateTable($tableName);
        $connection->query("ALTER TABLE $tableName AUTO_INCREMENT = 1");
    }

    /**
     * @return array
     */
    public function getEventTypeList()
    {
        $list = [];
        $eventData = $this->_typeCollection->create()
            ->addFieldToSelect(['event_type', 'status'])
            ->addFieldToFilter('status', 1)
            ->getData();
        foreach ($eventData as $event) {
            $list[] = $event['event_type'];
        }
        return $list;
    }

    /**
     * @param $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
        return $this->_assetRepo->getUrlWithParams($fileId, $params);
    }
}
