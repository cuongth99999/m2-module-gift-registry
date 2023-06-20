<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */

namespace Magenest\GiftRegistry\Block\Customer\Registry;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\AddressFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type\Collection;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class NewRegistry
 * @package Magenest\GiftRegistry\Block\Customer\Registry
 */
class NewRegistry extends Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CollectionFactory
     */
    protected $_registryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory
     */
    protected $_typeFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var null
     */
    protected $_typeModel = null;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var Customer
     */
    protected $_customerResources;

    /**
     * NewRegistry constructor.
     *
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param CollectionFactory $eventFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory
     * @param AddressFactory $addressFactory
     * @param CustomerFactory $customerFactory
     * @param Registry $registry
     * @param RegistrantFactory $registrantFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param Data $helperData
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
     * @param Json $json
     * @param Customer $customerResources
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        CollectionFactory $eventFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory,
        AddressFactory $addressFactory,
        CustomerFactory  $customerFactory,
        Registry $registry,
        RegistrantFactory $registrantFactory,
        GiftRegistryFactory $giftRegistryFactory,
        Data $helperData,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
        Json $json,
        Customer $customerResources,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_registryFactory = $eventFactory;
        $this->_typeFactory = $typeFactory;
        $this->addressFactory = $addressFactory;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_customerFactory = $customerFactory;
        $this->_coreRegistry = $registry;
        $this->_registrantFactory = $registrantFactory;
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
        $this->_json = $json;
        $this->_customerResources = $customerResources;
    }

    /**
     * @return int|null
     */
    public function getOwnerId()
    {
        return $this->currentCustomer->getCustomerId();
    }

    /**
     * @return Collection
     */
    public function getActiveEventType()
    {
        return $this->_typeFactory->create()->getActiveEventType();
    }

    /**
     * @return mixed
     */
    public function getShippingAddress()
    {
        return $this->addressFactory->create()->getCollection();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Add New Gift'));
        return parent::_prepareLayout();
    }

    /**
     * get the customer address
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerAddress()
    {
        $addressArr =[];
        $customerId =  $this->currentCustomer->getCustomerId();//->getAddressesCollection();

        $customer = $this->_customerFactory->create();
        $this->_customerResources->load($customer, $customerId);

        $addressCollection = $customer->getAddressesCollection();

        if ($addressCollection->getSize()) {

            /**
             * @var  $address Address
             */
            foreach ($addressCollection as $address) {
                $addressArr[] = ['name'=>$address->getName() . ' ' . $address->getStreetFull() . ' ' . $address->getRegion() . ' ' . $address->getCountry(),
                    'id'=>$address->getId()
                ];
            }
        }

        return $addressArr;
    }

    /**
     * @return mixed
     */
    public function getGiftRegistry()
    {
        $registryId = $this->getRequest()->getParam('event_id');

        if (!empty($registryId)) {
            $data = $this->_giftRegistryFactory->create()
                ->getCollection()
                ->addFieldToFilter('gift_id', $registryId)->getFirstItem();

            if (!is_object($data)) {
                $data =  $this->_giftRegistryFactory->create();
            }
            return $data;
        } else {
            return $this->_giftRegistryFactory->create();
        }
    }

    /**
     * @return mixed
     */
    public function getRegistryType()
    {
        return $this->_coreRegistry->registry('type');
    }

    /**
     * @return DataObject|null
     */
    public function getTypeModel()
    {
        if ($this->_typeModel == null) {
            $type = $this->getRegistryType();
            $typeModel = $this->_typeFactory->create()->addFieldToFilter('event_type', $type)->getFirstItem();
            if ($typeModel->getId()) {
                $this->_typeModel = $typeModel;
            }
        }
        return $this->_typeModel;
    }

    /**
     * @param $event
     * @return string
     */
    public function changePassword($event)
    {
        return $this->getUrl('gift_registry/customer/changepass', ['event_id' => $event]);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getFieldsAboutEvent()
    {
        $typeModel = $this->getTypeModel();
        if ($typeModel->getId() && $typeModel->getData('additions_field') != null) {
            $additions_field = $typeModel->getData('additions_field');
            return $this->_json->unserialize($additions_field);
        }
        return [];
    }

    /**
     * @return bool
     */
    public function isShowAboutEvent()
    {
        $typeModel = $this->getTypeModel();
        if ($typeModel->getId()&&$typeModel->getData('fields') != null) {
            $fields = $this->_json->unserialize($typeModel->getData('fields'));
            if (in_array('about_event', $fields)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed|string
     */
    public function getGoogleApi()
    {
        return $this->_helperData->getGoogleApi();
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
     * @param $field
     * @return false|string[]
     */
    public function getLabel($field)
    {
        $label = $field['label'];
        return explode(':', $label);
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
