<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:34
 */

namespace Magenest\GiftRegistry\Block\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\Type;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListGift
 * @package Magenest\GiftRegistry\Block\Registry
 */
class ListGift extends Template implements IdentityInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var SessionFactory
     */
    protected $_customerSession;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * ListGift constructor.
     * @param Data $helperData
     * @param CurrentCustomer $currentCustomer
     * @param TypeFactory $typeFactory
     * @param Session $session
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param Image $imageModel
     * @param SessionFactory $customerSession
     * @param ResourceConnection $resource
     */
    public function __construct(
        Data $helperData,
        CurrentCustomer $currentCustomer,
        TypeFactory $typeFactory,
        Session $session,
        GiftRegistryFactory $giftRegistryFactory,
        Context $context,
        ProductFactory $productFactory,
        Image $imageModel,
        SessionFactory $customerSession,
        ResourceConnection $resource
    ) {
        $this->_helperData = $helperData;
        $this->_productFactory = $productFactory;
        $this->_currentCustomer = $currentCustomer;
        $this->_registryFactory = $giftRegistryFactory;
        $this->_session = $session;
        $this->_typeFactory = $typeFactory;
        $this->imageModel = $imageModel;
        $this->_customerSession = $customerSession;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * return list of registry via customer id
     * @return AbstractDb|AbstractCollection|null
     */
    public function getRegistry()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        $registryCollection = $this->_registryFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
        return $registryCollection;
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        return __('Welcome to Gift Registry');
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
     * @return string
     */
    public function getListSearchUrl()
    {
        return $this->getUrl('gift-registry/search');
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event)
    {
        return $this->getUrl('gift_registry') . 'view' . str_replace('gift', '', $event['type']) . '.html?id=' . $event['gift_id'];
    }

    /**
     * @param $type
     * @return string
     * Get show-gift url via type of event.
     */
    public function getGiftUrl($type)
    {
        return $this->getUrl('gift-registry/event/') . $type . '.html';
    }

    /**
     * Get list type
     *
     * @return AbstractCollection
     * @throws NoSuchEntityException
     */
    public function getListEvent()
    {
        $listEvent = $this->_typeFactory->create()->getCollection()->addFieldToFilter('status', 1);
        if($this->_helperData->isHideEmptyEventType()){
            $listEvent->getSelect()
                ->joinRight(
                    ['mgTable' => $this->resource->getTableName('magenest_giftregistry')],
                    'main_table.event_type = mgTable.type',
                    ['mgTable.gift_id', 'mgTable.is_expired', 'mgTable.show_in_search']
                )->where('mgTable.is_expired = ?',0)
                ->where('mgTable.show_in_search like ?', 'on')
                ->group('main_table.event_type');
        };

        return $listEvent;
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
     * @param $path
     * @return string
     */
    public function getUrlImage($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customer = $this->_customerSession->create();
        $customerId = $customer->getCustomerId();
        return isset($customerId);
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
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [Type::CACHE_TAG];
    }
}
