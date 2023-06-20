<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:35
 */

namespace Magenest\GiftRegistry\Block\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ShowGift
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ShowGift extends Template implements IdentityInterface
{
    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var Type
     */
    protected $_typeResource;

    /** @var Image $imageModel */
    protected $imageModel;

    /** @var CollectionFactory $_collectionFactory */
    protected $_collectionFactory;

    /** @var Session $_session */
    protected $_session;

    /** @var CurrentCustomer $_currentCustomer */
    protected $_currentCustomer;

    /** @var Registry $_coreRegistry */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var SessionFactory
     */
    protected $_customerSession;

    /**
     * ShowGift constructor.
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param Image $imageModel
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param Session $session
     * @param CurrentCustomer $currentCustomer
     * @param Data $helperData
     * @param Context $context
     * @param SessionFactory $customerSession
     */
    public function __construct(
        TypeFactory $typeFactory,
        Type $typeResource,
        Image $imageModel,
        CollectionFactory $collectionFactory,
        Registry $registry,
        Session $session,
        CurrentCustomer $currentCustomer,
        Data $helperData,
        Context $context,
        SessionFactory $customerSession
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->imageModel = $imageModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_currentCustomer = $currentCustomer;
        $this->_session = $session;
        $this->_helperData = $helperData;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getStartedUrl()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        if (isset($customerId)!=0) {
            return $this->getUrl('gift_registry') . 'new' . str_replace('gift', '', $this->_coreRegistry->registry('type')) . '.html';
        }
        return $this->getUrl('customer/account/login');
    }

    /**
     * @return string
     */
    public function getListSearchUrl()
    {
        return $this->getUrl('gift-registry/search');
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customer = $this->_customerSession->create();
        $customerId = $customer->getCustomerId();
        return (isset($customerId)!=0) ? true : false;
    }

    /**
     * @return string
     */
    public function getViewGiftRegistryUrl()
    {
        $url = $this->getUrl('customer/account/login');
        if ($this->isLoggedIn()) {
            $url = $this->getUrl('gift_registry/customer/mygiftregistry');
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/index');
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login/');
    }

    /**
     * @return mixed|null
     */
    public function getEventType()
    {
        return $this->_coreRegistry->registry('type');
    }

    /**
     * @return string
     */
    public function getGiftRegistryPageUrl()
    {
        return $this->getUrl('gift-registry.html');
    }

    /**
     * @return \Magenest\GiftRegistry\Model\Type
     */
    public function getTypeModel()
    {
        $type = $this->getEventType();
        $typeModel = $this->_typeFactory->create();
        $this->_typeResource->load($typeModel, $type, 'event_type');
        return $typeModel;
    }

    /**
     * @param $path
     * @return string
     */
    public function getImageUrl($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
    }

    /**
     * @return false|mixed|null
     */
    public function getGiftId()
    {
        return $this->_coreRegistry->registry('gift_id') ? $this->_coreRegistry->registry('gift_id') : false;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getAllGiftRegistries()
    {
        $type = $this->getEventType();
        $collections = $this->_collectionFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('is_expired', '0')
            ->addFieldToFilter('show_in_search', 'on');
        return $collections->getItems();
    }

    /**
     * @param $giftId
     * @param $type
     *
     * @return string
     */
    public function getPreviewUrl($giftId, $type)
    {
        return $this->getUrl('gift-registry') . 'view/gift/' . $giftId . '/' . $type . '.html';
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getBackgroundImage()
    {
        return $this->_helperData->getBackgroundSearchGiftRegistryPage();
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [GiftRegistry::CACHE_TAG];
    }
}
