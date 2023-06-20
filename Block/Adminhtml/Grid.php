<?php

namespace Magenest\GiftRegistry\Block\Adminhtml;

use Exception;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type\Collection;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Block\Adminhtml
 */
class Grid extends Extended
{
    /**
     * @var string
     */
    protected $_template = 'Magenest_GiftRegistry::customer_transaction_tab.phtml';

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Customer
     */
    protected $_customerResource;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftRegistryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory
     */
    protected $_typeCollection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param CustomerFactory $customerFactory
     * @param CurrentCustomer $currentCustomer
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeCollection
     * @param CollectionFactory $collectionFactory
     * @param Customer $customerResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CustomerFactory $customerFactory,
        CurrentCustomer $currentCustomer,
        GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory $typeCollection,
        CollectionFactory $collectionFactory,
        Customer $customerResource,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->setEmptyText(__('No Transaction Found'));
        $this->setId('transaction_tab');
        $this->_customerFactory = $customerFactory;
        $this->currentCustomer = $currentCustomer;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_typeCollection = $typeCollection;
        $this->collectionFactory = $collectionFactory;
        $this->_customerResource = $customerResource;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getTransactionCollection($customerId)
    {
        $transactionCollection = $this->collectionFactory->create();
        $transactionCollection->addFieldToSelect('*');
        $transactionCollection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        return $transactionCollection;
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $transactionCollection = $this->getTransactionCollection($this->getCustomerId());
        $this->setCollection($transactionCollection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this|Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'Name',
            [
                'header' => __('Name'),
                'index'  => 'firstname',
                'renderer' => 'Magenest\GiftRegistry\Block\Adminhtml\Tab\Registrant'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index'  => 'email',
                'renderer' => 'Magenest\GiftRegistry\Block\Adminhtml\Tab\Tab'
            ]
        );
        $this->addColumn(
            'is_expired',
            [
                'header' => __('Status'),
                'index'  => 'is_expired',
                'renderer' => 'Magenest\GiftRegistry\Block\Adminhtml\Tab\Expired'
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index'  => 'title'
            ]
        );
        $this->addColumn(
            'date',
            [
                'header' => __('Date'),
                'index'  => 'date'
            ]
        );
        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index'  => 'description'
            ]
        );
        $this->addColumn(
            'shipping_address',
            [
                'header' => __('Shipping Address'),
                'index'  => 'shipping_address',
                'renderer' => 'Magenest\GiftRegistry\Block\Adminhtml\Tab\Address'
            ]
        );

        return $this;
    }

    /**
     * @return Extended
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'add_gift',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
                [
                    'label' => __('Add Gift Registry'),
                    'class' => 'task action-secondary primary',
                    'id'    => 'add_gift'
                ]
            )->setDataAttribute(
                [
                    'action' => 'add_gift'
                ]
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = $this->getAddPointHtml();
        if ($this->getFilterVisibility()) {
            $html .= $this->getResetFilterButtonHtml();
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getAddPointHtml()
    {
        return $this->getChildHtml('add_gift');
    }

    /**
     * @return string
     */
    public function getAddPointUrl()
    {
        return $this->getUrl('giftregistry/transaction/save');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerAddress()
    {
        $addressArr =[];
        $customerId =  $this->getCustomerId();//->getAddressesCollection();
        $customer = $this->_customerFactory->create();
        $this->_customerResource->load($customer, $customerId);
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
     * @return GiftRegistry|DataObject
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
     * @return Collection
     */
    public function getEventType()
    {
        return $this->_typeCollection->create()->addFieldToFilter('status', '1');
    }
}
