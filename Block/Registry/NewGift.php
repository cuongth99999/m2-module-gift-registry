<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:37
 */

namespace Magenest\GiftRegistry\Block\Registry;

use Magenest\GiftRegistry\Model\AddressFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class NewGift
 * @package Magenest\GiftRegistry\Block\Registry
 */
class NewGift extends Template
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
     * @var Customer
     */
    protected $_customerResources;

    /**
     * NewGift constructor.
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param CollectionFactory $eventFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory
     * @param AddressFactory $addressFactory
     * @param CustomerFactory $customerFactory
     * @param Customer $customerResources
     * @param Registry $registry
     * @param RegistrantFactory $registrantFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        CollectionFactory $eventFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeFactory,
        AddressFactory $addressFactory,
        CustomerFactory  $customerFactory,
        Customer $customerResources,
        Registry $registry,
        RegistrantFactory $registrantFactory,
        GiftRegistryFactory $giftRegistryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_registryFactory = $eventFactory;
        $this->_typeFactory = $typeFactory;
        $this->addressFactory = $addressFactory;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customerResources = $customerResources;
        $this->_coreRegistry = $registry;
        $this->_registrantFactory = $registrantFactory;
    }

    /**
     * @return mixed
     */
    public function getShippingAddress()
    {
        return $this->addressFactory->create()->getCollection();
    }

    /**
     * @return mixed
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
     * @param $event
     * @return string
     */
    public function changePassword($event)
    {
        return $this->getUrl('gift_registry/customer/changepass', ['event_id' => $event]);
    }
}
