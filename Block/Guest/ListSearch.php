<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 29/04/2016
 * Time: 14:55
 */

namespace Magenest\GiftRegistry\Block\Guest;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\Registrant;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\Type;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListSearch
 * @package Magenest\GiftRegistry\Block\Guest
 */
class ListSearch extends Template implements IdentityInterface
{
    const FILTER_BY_TITLE = 1;
    const FILTER_BY_NAME = 2;

    /** @var CurrentCustomer  */
    protected $currentCustomer;

    /** @var CollectionFactory  */
    protected $_eventFactory;

    /** @var RegistrantFactory  */
    protected $_registrantFactory;

    /** @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory  */
    protected $_customerFactory;

    /** @var TypeFactory  */
    protected $_typeFactory;

    /** @var Image  */
    protected $imageModel;

    /** @var Registry  */
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
     * ListSearch constructor.
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param CollectionFactory $eventFactory
     * @param RegistrantFactory $registrantFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory
     * @param TypeFactory $typeFactory
     * @param Image $imageModel
     * @param Registry $registry
     * @param Data $helperData
     * @param SessionFactory $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        CollectionFactory $eventFactory,
        RegistrantFactory $registrantFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory,
        TypeFactory $typeFactory,
        Image $imageModel,
        Registry $registry,
        Data $helperData,
        SessionFactory $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_eventFactory = $eventFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        $this->_typeFactory = $typeFactory;
        $this->imageModel = $imageModel;
        $this->_coreRegistry = $registry;
        $this->_helperData = $helperData;
        $this->_customerSession = $customerSession;
    }

    /**
     * @param $registryId
     * @return mixed
     */
    public function getInforEvent($registryId)
    {
        return $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('show_in_search', 'on')
            ->addFieldToFilter('is_expired', '0');
    }

    /**
     * @param $registryId
     * @param $type
     * @return mixed
     */
    public function getInforEventByType($registryId, $type)
    {
        $infor = $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('show_in_search', 'on')
            ->addFieldToFilter('is_expired', '0')
            ->getData();
        return array_pop($infor);
    }

    /**
     * Get all list result search
     *
     * @return array
     */
    public function getListRegistry()
    {
        $query = $this->getRequest()->getParams();
        $result = null;
        if ($query['filter'] == self::FILTER_BY_TITLE) {
            $title = $query['t'];
            $result = $this->searchByTitle($title, $query['type']);
        } else {
            $firstName = $this->getFirstname();
            $lastName = $this->getLastname();
            if ($query['type']) {
                $result = $this->searchByName($firstName, $lastName, $query['type']);
            }
        }
        return $result;
    }

    /**
     * @param $title
     * @param $type
     * @return array
     */
    public function searchByTitle($title, $type)
    {
        $title = trim($title);
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $titleResult = $this->_registrantFactory->create()->getCollection();
        $titleResult->getSelect()
            ->join(
                [
                'registry' => $titleResult->getTable("magenest_giftregistry")
            ],
                'main_table.giftregistry_id = registry.gift_id'
            )->where(
                "title like ?",
                '%' . $title . '%'
            )->where(
                "registry.is_expired = '0'"
            )->where(
                "registry.show_in_search = 'on'"
            );
        if ($type != "all") {
            $titleResult->getSelect()->where("type = ?", $type);
        }
        return $titleResult->getData();
    }

    /**
     * @param $firstName
     * @param $lastName
     * @param $type
     * @return array
     */
    public function searchByName($firstName, $lastName, $type)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $collection = $this->_registrantFactory->create()->getCollection();
        $collection->getSelect()
            ->joinLeft(
                [
                'registry' => $collection->getTable('magenest_giftregistry')
            ],
                'main_table.giftregistry_id = registry.gift_id'
            )->where(
                "main_table.firstname = ?",
                $firstName
            )->where(
                "main_table.lastname = ?",
                $lastName
            )->where(
                "registry.is_expired = '0'"
            )->where(
                "registry.show_in_search = 'on'"
            );
        if ($type != "all") {
            $collection->getSelect()->where("registry.type = ?", $type);
        }
        return $collection->getData();
    }

    /**
     * @return mixed
     */
    public function getTypeEvent()
    {
        $params = $this->getRequest()->getParams();
        return $params['type'];
    }

    /**
     * Get firstname keyword search
     * @return mixed
     */
    public function getFirstname()
    {
        $params = $this->getRequest()->getParams();
        if (key_exists('fn', $params)) {
            return $params['fn'];
        }
        if (key_exists('event_fn', $params)) {
            return $params['event_fn'];
        }
        return '';
    }

    /**
     * Get lastname keyword search
     * @return mixed
     */
    public function getLastname()
    {
        $params = $this->getRequest()->getParams();
        if (key_exists('ln', $params)) {
            return $params['ln'];
        }
        if (key_exists('event_ln', $params)) {
            return $params['event_ln'];
        }
        return '';
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getRequest()->getParam('t');
    }

    /**
     * @return bool
     */
    public function isFilterByName()
    {
        return $this->getRequest()->getParam('filter') == self::FILTER_BY_NAME;
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl('*/index/search');
    }

    /**
     * @return AbstractCollection
     * Get list type
     */
    public function getListEvent()
    {
        return $this->_typeFactory->create()->getCollection();
    }

    /**
     * @param $str
     * @return false|string|string[]|null
     */
    public function stripUnicode($str)
    {
        if (!$str) {
            return false;
        }
        $unicode = [
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonUnicode=>$uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        return $str;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customer = $this->_customerSession->create();
        $customerId = $customer->getCustomerId();
        return isset($customerId)!=0;
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
    public function getListSearchUrl()
    {
        return $this->getUrl('gift-registry/search');
    }

    /**
     * @return string
     */
    public function getGiftRegistryPageUrl()
    {
        return $this->getUrl('gift-registry.html');
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
     * @param $path
     * @return string
     */
    public function getImageUrl($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
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
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [Registrant::CACHE_TAG, Type::CACHE_TAG];
    }
}
