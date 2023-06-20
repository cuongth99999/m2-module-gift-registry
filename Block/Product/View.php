<?php
namespace Magenest\GiftRegistry\Block\Product;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;

/**
 * Class View
 * @package Magenest\GiftRegistry\Block\Product
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /** @var ItemFactory  */
    protected $_itemFactory;

    /** @var Item  */
    protected $_itemResource;

    /**
     * @var Item\CollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var CollectionFactory
     */
    protected $_giftRegistryCollection;

    /** @var Data  */
    protected $_helperData;

    /**
     * View constructor.
     * @param ItemFactory $itemFactory
     * @param Item $itemResource
     * @param Item\CollectionFactory $collectionFactory
     * @param CollectionFactory $giftRegistryCollection
     * @param Data $helperData
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        ItemFactory $itemFactory,
        Item $itemResource,
        Item\CollectionFactory $collectionFactory,
        CollectionFactory $giftRegistryCollection,
        Data $helperData,
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->_itemCollection = $collectionFactory;
        $this->_helperData = $helperData;
        $this->_giftRegistryCollection = $giftRegistryCollection;
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
    }

    /**
     * @return array
     */
    public function checkProductInGiftRegistry()
    {
        $product = $this->getProduct();
        $customer = $this->_helperData->getCustomer();
        if ($product->getEntityId()&&$customer != null &&$customer->getId()) {
            $productId = $product->getEntityId();
            $customerId = $customer->getId();
            $itemTable = $this->_itemResource->getTable('magenest_giftregistry_item');
            $connection = $this->_itemResource->getConnection();
            $sql = $this->_giftRegistryCollection->create()->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->joinLeft(
                    ['i' =>$itemTable],
                    'main_table.gift_id = i.gift_id',
                    []
                )->where(
                    "`i`.`product_id` = '" . $productId . "' AND `main_table`.`customer_id` = '" . $customerId . "' AND `main_table`.`is_expired` = '0'"
                )->group(
                    "main_table.gift_id"
                )->columns([
                    'main_table.gift_id as gift_id',
                    'main_table.title as title',
                    'main_table.type as type',
                ])->__toString();
            return $connection->fetchAll($sql);
        }
    }

    /**
     * @param $gift_id
     * @param $productId
     * @return array
     */
    public function getItemOptionByGiftId($gift_id, $productId)
    {
        $collections = $this->_itemCollection->create()->addFieldToFilter('gift_id', $gift_id)->addFieldToFilter('product_id', $productId);
        $options = [];
        foreach ($collections as $collection) {
            $option = $this->getItemOptions($collection);
            $giftItemId = $collection->getData('gift_item_id');
            if (!empty($option)) {
                $options[$giftItemId] = $option;
            }
        }
        return $options;
    }

    /**
     * @param $item
     * @return array
     */
    public function getItemOptions($item)
    {
        return $this->_helperData->getOptions($item);
    }

    /**
     * @param $gift_id
     * @param $productId
     * @return DataObject
     */
    public function getItemInGR($gift_id, $productId)
    {
        return $this->_itemCollection->create()
            ->addFieldToFilter('gift_id', $gift_id)
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem();
    }

    /**
     * @param $gift_item_id
     * @return \Magenest\GiftRegistry\Model\Item
     */
    public function getItemByGiftItemId($gift_item_id)
    {
        $itemModel = $this->_itemFactory->create();
        $this->_itemResource->load($itemModel, $gift_item_id);
        return $itemModel;
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
