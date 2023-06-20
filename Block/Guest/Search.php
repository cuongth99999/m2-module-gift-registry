<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 14:10
 */
namespace Magenest\GiftRegistry\Block\Guest;

use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Search
 * @package Magenest\GiftRegistry\Block\Guest
 */
class Search extends Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CollectionFactory
     */
    protected $_eventFactory;

    /**
     * Search constructor.
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param CollectionFactory $eventFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        CollectionFactory $eventFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_eventFactory = $eventFactory;
    }

    /**
     * @return string
     */
    public function getIdEvent()
    {
        $info1 = $this->getRequest()->getParam('event_fn');
        $info2 = $this->getRequest()->getParam('event_ln');
        if ($info1 != null && $info2 != null) {
            return $this->getUrl('gift_registry/customer/registry/', ['ship_firstname' => $info1], ['ship_lastname' => $info2]);
        } else {
            return 'gift_registry/customer/registry';
        }
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
     * @return Search
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
