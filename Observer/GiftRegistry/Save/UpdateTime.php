<?php
/**
 * Created by PhpStorm.
 * User: duchai
 * Date: 27/12/2018
 * Time: 13:31
 */
namespace Magenest\GiftRegistry\Observer\GiftRegistry\Save;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\Registrant;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateTime
 * @package Magenest\GiftRegistry\Observer\GiftRegistry\Save
 */
class UpdateTime implements ObserverInterface
{

    /** @var RegistrantFactory $registrantFactory */
    protected $registrantFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Registrant
     */
    protected $registrantResource;

    /** @var Data $dataHelper */
    protected $dataHelper;

    /** @var CollectionFactory $_giftregistryTmpCollection */
    protected $_giftregistryTmpCollection;

    /** @var GiftRegistryTmp $_giftregistryTmpResource */
    protected $_giftregistryTmpResource;

    /** @var LoggerInterface $_logger */
    protected $_logger;

    /**
     * UpdateTime constructor.
     *
     * @param RegistrantFactory $registrantFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Registrant $registrantResource
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param GiftRegistryTmp $giftRegistryTmpResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        RegistrantFactory $registrantFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Registrant $registrantResource,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        GiftRegistryTmp $giftRegistryTmpResource,
        LoggerInterface $logger
    ) {
        $this->registrantFactory = $registrantFactory;
        $this->registrantResource = $registrantResource;
        $this->dataHelper = $dataHelper;
        $this->_giftregistryTmpCollection = $collectionFactory;
        $this->_giftregistryTmpResource = $giftRegistryTmpResource;
        $this->_logger = $logger;
    }

    /**
     * @param Observer $observer
     * @throws AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        try {
            $giftRegistry = $observer->getEvent()->getGiftRegistry();
            if (!($giftRegistry instanceof GiftRegistry) || !$giftRegistry->getId()) {
                return;
            }
            if ($giftRegistry->getUpdatedAt() && $giftRegistry->getUpdatedAt() == $giftRegistry->getOrigData('updated_at')) {
                return;
            }
            $updatedTime = $giftRegistry->getUpdatedAt();
            $registrant = $this->getRegistrantModel($giftRegistry);
            $registrant->setUpdatedTime($updatedTime);
            $this->registrantResource->save($registrant);
            $this->removeGiftRegistryTmp($giftRegistry);
        } catch (\Exception $exception) {
        }
        // update gift expired or not depend date
        $this->dataHelper->updateExpiredGift();
    }

    /**
     * @param GiftRegistry $giftRegistry
     * @return Registrant
     * @throws \Exception
     */
    private function getRegistrantModel(GiftRegistry $giftRegistry)
    {
        $registrant = $this->registrantFactory->create();
        $this->registrantResource->load($registrant, $giftRegistry->getId());
        if (!($registrant instanceof Registrant) || !$registrant->getId()) {
            throw new \Exception(__("Registrant Not Exist"));
        }
        return $registrant;
    }

    /**
     * @param GiftRegistry $giftRegistry
     * @throws \Exception
     */
    public function removeGiftRegistryTmp(GiftRegistry $giftRegistry)
    {
        $gift_id = $giftRegistry->getGiftId();
        $collections = $this->_giftregistryTmpCollection->create()->addFieldToFilter("gift_id", $gift_id)->getItems();
        $ids = [];
        foreach ($collections as $collection) {
            $ids[] = $collection->getGiftIdTmp();
        }
        if (!empty($ids)) {
            $this->_giftregistryTmpResource->removeMultiRecords($ids);
        }
    }
}
