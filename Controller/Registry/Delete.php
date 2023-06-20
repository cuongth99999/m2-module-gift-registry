<?php
/**
 * Created by PhpStorm.
 * User: duchai
 * Date: 22/01/2019
 * Time: 15:47
 */

namespace Magenest\GiftRegistry\Controller\Registry;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Registry
 */
class Delete extends AbstractAction
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var TranFactory
     */
    protected $giftRegistryOrderFactory;

    /**
     * @var null
     */
    protected $registry = null;

    /**
     * @var RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * Delete constructor.
     * @param Session $session
     * @param LoggerInterface $logger
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param TranFactory $tranFactory
     * @param RegistrantFactory $registrantFactory
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Session $session,
        LoggerInterface $logger,
        GiftRegistryFactory $giftRegistryFactory,
        TranFactory $tranFactory,
        RegistrantFactory $registrantFactory,
        Context $context,
        Data $data
    ) {
        $this->giftRegistryOrderFactory = $tranFactory;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->logger = $logger;
        $this->customerSession = $session;
        $this->registrantFactory = $registrantFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultRedirectFactory->create();
        $resultPage->setUrl($this->_redirect->getRefererUrl());
        try {
            $this->deleteRegistry();
            $this->messageManager->addSuccessMessage(__("A registry have been deleted."));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Can't delete Registry now."));
        }
        return $resultPage;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getCustomerId()
    {
        $customerId = $this->customerSession->getId();
        if (!$customerId) {
            throw new LocalizedException(__('Please Login To Do This Action.'));
        }
        return $customerId;
    }

    /**
     * @throws LocalizedException
     */
    protected function deleteRegistry()
    {
        $registry = $this->getRegistry();
//        $this->validateDeleteRegistry();
        //delete registrant of gift registry
        $this->deleteRegistrant($registry);
        $registry->delete();
    }

    /**
     * @return DataObject|null
     * @throws LocalizedException
     */
    protected function getRegistry()
    {
        if ($this->registry === null) {
            $registryId = $this->getRequest()->getParam('id');
            if (!$registryId || !is_numeric($registryId)) {
                throw new LocalizedException(__('Invalid Registry Id.'));
            }
            $registry = $this->giftRegistryFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $this->getCustomerId())
                ->addFieldToFilter('gift_id', $registryId)
                ->getFirstItem();
            if (!$registry || !$registry->getId()) {
                throw new LocalizedException(__('Registry Not Existed.'));
            }
            $this->registry = $registry;
        }
        return $this->registry;
    }

    /**
     * @throws LocalizedException
     */
    protected function validateDeleteRegistry()
    {
        if ($this->isEventTimePassed()) {
            return;
        }
        $this->validateOrder();
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function isEventTimePassed()
    {
        $registry = $this->getRegistry();
        $date = $registry->getDate();
        $today = date('Y-m-d');
        $today = date_create_from_format('Y-m-d H:i:s', "$today 00:00:00");
        $date = date_create_from_format('Y-m-d H:i:s', date_create_from_format('Y-m-d H:i:s', $date)->format('Y-m-d') . ' 00:00:00');
        $dateInterval = $today->diff($date);
        $dateDiff = $dateInterval->format('%a');
        if ($dateDiff > 0) {
            return true;
        }
        return false;
    }

    /**
     * @throws LocalizedException
     */
    protected function validateOrder()
    {
        $registry = $this->getRegistry();
        $collection = $this->giftRegistryOrderFactory->create()->getCollection()
            ->addFieldToFilter('giftregistry_id', $registry->getId());
        if ($collection->getSize() > 0) {
            throw new LocalizedException(__("Can't delete registry with order placed."));
        }
    }

    /**
     * @param $registry
     */
    protected function deleteRegistrant($registry)
    {
        $this->registrantFactory->create()->getCollection()
            ->addFieldToFilter('giftregistry_id', $registry->getId())
            ->walk('delete');
    }
}
