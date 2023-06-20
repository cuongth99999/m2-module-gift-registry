<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 25/04/2016
 * Time: 16:35
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\Status;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Items
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab
 */
class Items extends Generic implements TabInterface
{
    const COMM_TEMPLATE = 'customer/listitems.phtml';

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var Registrant
     */
    protected $_registrantResources;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var GiftRegistry
     */
    protected $_giftRegistryResources;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ProductResource
     */
    protected $_productResources;

    /**
     * Items constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param CustomerFactory $customerFactory
     * @param ProductFactory $productFactory
     * @param RegistrantFactory $registrantFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param CollectionFactory $itemFactory
     * @param ProductResource $productResources
     * @param Registrant $registrantResources
     * @param GiftRegistry $giftRegistryResources
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        RegistrantFactory $registrantFactory,
        GiftRegistryFactory $giftRegistryFactory,
        CollectionFactory $itemFactory,
        ProductResource $productResources,
        Registrant $registrantResources,
        GiftRegistry $giftRegistryResources,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_customerFactory =$customerFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_itemFactory = $itemFactory;
        $this->_productFactory = $productFactory;
        $this->_productResources = $productResources;
        $this->_registrantResources = $registrantResources;
        $this->_giftRegistryResources = $giftRegistryResources;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this|Items
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_prepareForm();
        $this->setTemplate(static::COMM_TEMPLATE);
        return $this;
    }

    /**
     * params RegistrantId
     *
     * @return array list Items Gift Registry
     */
    public function getListItems()
    {
        $registrantId = $this->getRequest()->getParam('registrant_id');
        $connectRegistrant = $this->_registrantFactory->create();
        $this->_registrantResources->load($connectRegistrant, $registrantId);
        $connectGiftRegistry = $this->_giftRegistryFactory->create();
        $this->_giftRegistryResources->load($connectGiftRegistry, $connectRegistrant->getGiftregistryId());
        return $this->_itemFactory->create()
            ->addFieldToFilter('gift_id', $connectGiftRegistry->getGiftId())->getData();
    }

    /**
     * @param $productId
     * @return Product
     */
    public function getInfoProduct($productId)
    {
        $product = $this->_productFactory->create();
        $this->_productResources->load($product, $productId);
        return $product;
    }

    /**
     * @param $idProduct
     * @return string
     */
    public function getViewItem($idProduct)
    {
        $data = $this->getUrl('catalog/product/edit', ['id' => $idProduct]);
        return $data;
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Items');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Items');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
