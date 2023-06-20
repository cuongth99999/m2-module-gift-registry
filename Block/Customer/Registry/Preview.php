<?php
namespace Magenest\GiftRegistry\Block\Customer\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\Config\Priority;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\GiftRegistryTmpFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResources;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Zend_Filter_Interface;

class Preview extends ViewRegistry implements DataObject\IdentityInterface
{
    /** @var GiftRegistryTmpFactory $_giftregistryTmpFactory */
    protected $_giftregistryTmpFactory;

    /** @var GiftRegistryTmp $_giftregistryTmpResource */
    protected $_giftregistryTmpResource;

    /**
     * @var Zend_Filter_Interface
     */
    protected $templateProcessor;

    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    protected $customerRepositoryFactory;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * Preview constructor.
     * @param ProductResources $productResources
     * @param GiftRegistryTmpFactory $giftRegistryTmpFactory
     * @param GiftRegistryTmp $giftRegistryTmpResource
     * @param RegistrantFactory $registrantFactory
     * @param Context $context
     * @param FormKey $formKey
     * @param CurrentCustomer $currentCustomer
     * @param Data $helper
     * @param Item\CollectionFactory $itemFactory
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
     * @param Zend_Filter_Interface $templateProcessor
     * @param Serialize $serialize
     * @param CustomerRepositoryInterfaceFactory $customerRepositoryFactory
     * @param Type $resourceType
     * @param CountryFactory $countryFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        ProductResources $productResources,
        GiftRegistryTmpFactory $giftRegistryTmpFactory,
        GiftRegistryTmp $giftRegistryTmpResource,
        RegistrantFactory $registrantFactory,
        Context $context,
        FormKey $formKey,
        CurrentCustomer $currentCustomer,
        Data $helper,
        Item\CollectionFactory $itemFactory,
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
        Zend_Filter_Interface $templateProcessor,
        Serialize $serialize,
        CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
        Type $resourceType,
        CountryFactory $countryFactory,
        Json $json,
        array $data = []
    ) {
        $this->_giftregistryTmpFactory = $giftRegistryTmpFactory;
        $this->_giftregistryTmpResource = $giftRegistryTmpResource;
        $this->templateProcessor = $templateProcessor;
        $this->customerRepositoryFactory = $customerRepositoryFactory;
        $this->_configurable = $configurable;
        parent::__construct($registrantFactory, $context, $formKey, $currentCustomer, $helper, $itemFactory, $priority, $typeFactory, $productFactory, $productRepositoryInterface, $eavAttribute, $configurable, $imageModel, $giftRegistryFactory, $giftRegistryResource, $stockRegistry, $serialize, $resourceType, $countryFactory, $productResources, $json, $data);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDisplayQtyMode()
    {
        return $this->helper->getDisplayQtyMode();
    }

    /**
     * @param int $productId
     * @return string
     */
    public function addToCart($productId = 0)
    {
        if ($productId) {
            return $this->getUrl('gift_registry/cart/add', ['product_id' =>$productId]);
        } else {
            return $this->getUrl('gift_registry/cart/add');
        }
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return  $this->_formKey->getFormKey();
    }

    /**
     * @return mixed|string
     */
    public function getCurrentUrl()
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $arr = explode('?', $currentUrl);
        if (is_array($arr)&&isset($arr[1])) {
            $currentUrl = $arr[0];
        }
        return $currentUrl;
    }

    /**
     * @return \Magenest\GiftRegistry\Model\GiftRegistryTmp
     */
    public function getRegistryTmp()
    {
        $params = $this->getRequest()->getParams();
        $giftregistryTmp = $this->_giftregistryTmpFactory->create();
        if (isset($params['gift_id'])) {
            $gift_id = $params['gift_id'];
            $this->_giftregistryTmpResource->load($giftregistryTmp, $gift_id);
        }
        $formattedDate = date_format(date_create($giftregistryTmp->getDate()), "Y-m-d");
        $giftregistryTmp->setData('date', $formattedDate);
        return $giftregistryTmp;
    }

    /**
     * @param $registry
     * @return \Magenest\GiftRegistry\Model\GiftRegistry
     */
    public function getGiftRegistry($registry)
    {
        $giftId = $registry->getData('gift_id');
        $dataGift = $this->_giftregistryFactory->create();
        $this->_giftregistryResource->load($dataGift, $giftId);
        return $dataGift;
    }

    /**
     * @param $giftregistry
     * @return DataObject
     */
    public function getCustomer($giftregistry)
    {
        $giftRegistryId = $giftregistry->getData('gift_id');
        $registrant = $this->registrantFactory->create()->getCollection()->addFieldToFilter("giftregistry_id", $giftRegistryId)->getFirstItem();
        return $registrant;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGiftRegistryItem()
    {
        $params = $this->getRequest()->getParams();
        return $this->helper->getGiftRegistryItem($params);
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
     * @param $path
     * @return string
     */
    public function getImageGRUrl($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
    }

    /**
     * @param $string
     * @return mixed
     * @throws \Zend_Filter_Exception
     */
    public function filterOutputHtml($string)
    {
        if (isset($string) && !empty($string))
        {
            return $this->templateProcessor->filter($string);
        }
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return $this->getRegistryTmp()->getIdentities();
    }

    /**
     * @return \Magenest\GiftRegistry\Model\Type
     */
    public function getEventTypeModel()
    {
        $type = $this->getEventType();
        $typeModel = $this->_typeFactory->create();
        $this->_resourceType->load($typeModel, $type, 'event_type');
        return $typeModel;
    }

    /**
     * @return mixed
     * Get event type
     */
    public function getEventType()
    {
        return $this->getGiftRegistry($this->getRegistryTmp())->getData('type');
    }
}
