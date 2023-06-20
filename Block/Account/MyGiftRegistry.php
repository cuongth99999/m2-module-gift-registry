<?php

/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 14:25
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Block\Account;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;

/**
 * Class MyGiftRegistry
 * @package Magenest\GiftRegistry\Block\Account
 */
class MyGiftRegistry extends Template implements IdentityInterface
{
    /** @var GiftRegistryFactory  */
    protected $giftregistryFactory;

    /** @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory  */
    protected $giftregistryCollection;

    /** @var Session  */
    protected $customerSession;

    /** @var TypeFactory  */
    protected $_typeFactory;

    /** @var Type  */
    protected $_typeResource;

    /** @var CollectionFactory  */
    protected $_typeCollection;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var Data
     */
    protected $helpData;

    /**
     * @var SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * MyGiftRegistry constructor.
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $giftregistryCollection
     * @param Session $session
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param CollectionFactory $typeCollection
     * @param Image $imageModel
     * @param Data $helpData
     * @param Template\Context $context
     * @param SessionFactory $customerSessionFactory
     * @param array $data
     */
    public function __construct(
        GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $giftregistryCollection,
        Session $session,
        TypeFactory $typeFactory,
        Type $typeResource,
        CollectionFactory $typeCollection,
        Image $imageModel,
        Data $helpData,
        Template\Context $context,
        SessionFactory $customerSessionFactory,
        array $data = []
    ) {
        $this->giftregistryFactory = $giftRegistryFactory;
        $this->giftregistryCollection = $giftregistryCollection;
        $this->customerSession = $session;
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->_typeCollection = $typeCollection;
        $this->imageModel = $imageModel;
        $this->helpData = $helpData;
        $this->_customerSessionFactory = $customerSessionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getListRegistry()
    {
        $params = $this->getRequest()->getParams();
        $customerSession = $this->_customerSessionFactory->create();
        if (empty($params)||$params['filter_status']=="") {
            $collection = $this->giftregistryCollection->create()
                ->addFieldToFilter('customer_id', $customerSession->getCustomerId());
        } else {
            $status = $params['filter_status'];
            if ($status == "0" || $status == "1") {
                $collection = $this->giftregistryCollection->create()
                    ->addFieldToFilter('customer_id', $customerSession->getCustomerId())
                    ->addFieldToFilter('is_expired', $status);
            } else {
                $collection = $this->giftregistryCollection->create()
                    ->addFieldToFilter('customer_id', $customerSession->getCustomerId());
            }
        }
        $listRegistry = [];
        $eventList = $this->helpData->getEventTypeList();
        foreach ($collection as $registry) {
            $type = $registry->getType();
            if (array_search($type, $eventList) !== false) {
                $listRegistry[] = $registry->getData();
            }
        }
        return $listRegistry;
    }

    /**
     * @return mixed|string
     */
    public function getParamsRequest()
    {
        $params = $this->getRequest()->getParams();
        $result = "";
        if (!empty($params)&&isset($params['filter_status'])&&($params['filter_status'] == "0" || $params['filter_status'] == "1")) {
            $result = $params['filter_status'];
        }
        return $result;
    }

    /**
     * @param $giftId
     * @param $type
     * @return string
     */
    public function getPreviewUrl($giftId, $type)
    {
        return $this->getUrl('gift-registry') . 'view/gift/' . $giftId . '/' . $type . '.html';
    }

    /**
     * @param $type
     * @param $giftId
     * @return string
     */
    public function getManageUrl($type, $giftId)
    {
        $manageRegistryUrl = $this->getUrl();
        return $manageRegistryUrl . "gift-registry/manage/id/" . $giftId;
    }

    /**
     * @return AbstractCollection
     * Get list type
     */
    public function getListEvent()
    {
        return $this->_typeCollection->create()->addFieldToFilter('status', '1');
    }

    /**
     * @param $type
     * @return string
     * Get show-gift url via type of event.
     */
    public function getGiftUrl($type)
    {
        return $this->getUrl('gift-registry/') . "new/" . $type . '.html';
    }

    /**
     * @param $giftRegistryId
     * @return string
     */
    public function getDeleteUrl($giftRegistryId)
    {
        return $this->getUrl('gift_registry/registry/delete', ['id' => $giftRegistryId]);
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
     * @param $type
     * @return mixed
     */
    public function getEventName($type)
    {
        $typeModel = $this->_typeFactory->create();
        $this->_typeResource->load($typeModel, $type, 'event_type');
        if ($typeModel->getId()) {
            return $typeModel->getData('event_title');
        }
        return $type;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [GiftRegistry::CACHE_TAG];
    }
}
