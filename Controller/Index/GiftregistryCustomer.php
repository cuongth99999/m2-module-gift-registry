<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 17/08/2018
 * Time: 09:33
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Class GiftregistryCustomer
 * @package Magenest\GiftRegistry\Controller\Index
 */
class GiftregistryCustomer extends AbstractAction
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $giftRegistry;

    /**
     * GiftregistryCustomer constructor.
     * @param Context $context
     * @param Data $data
     * @param Session $customerSession
     * @param CollectionFactory $giftRegistryFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        Session $customerSession,
        CollectionFactory $giftRegistryFactory
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->giftRegistry = $giftRegistryFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $customerId = $this->customerSession->getId();
        $giftRegistrys = $this->giftRegistry->create()
           ->addFieldToFilter("customer_id", $customerId)
           ->addFieldToFilter('is_expired', 0);
        $data = [];
        $i=1;
        foreach ($giftRegistrys as $giftRegistry) {
            $data[$giftRegistry->getGiftId()] = $giftRegistry->getType();
            $i++;
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }
}
