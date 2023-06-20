<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 7/17/17
 * Time: 1:11 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;
use Psr\Log\LoggerInterface;

/**
 * Class Search
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Search extends AbstractAction
{
    const FILTER_BY_TITLE = 1;
    const FILTER_BY_NAME = 2;

    /**
     * @var CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $_customerFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Search constructor.
     * @param LoggerInterface $logger
     * @param CollectionFactory $eventFactory
     * @param RegistrantFactory $registrantFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $eventFactory,
        RegistrantFactory $registrantFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerFactory,
        Context $context,
        Data $data
    ) {
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->_customerFactory = $customerFactory;
        return parent::__construct($context, $data);
    }

    /**
     * @return ResultInterface|Layout|Page
     */
    public function execute()
    {
        $query = $this->getRequest()->getParams();
        $result = null;
        if ($query['filter_selected'] == self::FILTER_BY_TITLE) {
            $title = $query['title'];
            $result = $this->searchByTitle($title, $query['type']);
        } else {
            $firstName = $query['firstName'];
            $lastName = $query['lastName'];
            $result = $this->searchByName($firstName, $lastName, $query['type']);
        }
        $information = [];
        foreach ($result as $registrant) {
            $event = $this->getInforEvent($registrant['giftregistry_id']);
            if ($event->getId()) {
                $eventdata = $event->getData();
                $registrant['type'] = $eventdata['type'];
                $registrant['title'] = $eventdata['title'];
                $registrant['location'] = $eventdata['location'];
                $registrant['date'] = $eventdata['date'];
                $registrant['url'] = $this->getViewUrl($eventdata['password'], $eventdata['gift_id'], $eventdata['type']);
                array_push($information, $registrant);
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($information);
        return $resultJson;
    }

    /**
     * @param $title
     * @param $type
     * @return array
     */
    public function searchByTitle($title, $type)
    {
        $title = trim($title);
        $titleResult = $this->_registrantFactory->create()
            ->getCollection();
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        if ($type != "*") {
            $titleResult->getSelect()
                ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'giftregistry_id = registry.gift_id')
                ->where("title LIKE ?", "%" . $title . "%")
                ->where("type = ?", $type);
            return $titleResult->getData();
        }
        $titleResult->getSelect()
            ->join(['registry' => $titleResult->getTable("magenest_giftregistry")], 'giftregistry_id = registry.gift_id')
            ->where("title LIKE ?", "%" . $title . "%");
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
        $firstNameResult = $this->_registrantFactory->create()
            ->getCollection()
            ->addFieldToFilter('firstname', ['like' => '%' . $firstName . '%']);
        $lastNameResult = $this->_registrantFactory->create()
            ->getCollection()
            ->addFieldToFilter('lastname', ['like' => '%' . $lastName . '%']);

        if ($type != "*") {
            $firstNameResult->getSelect()
                ->join(['registry' => $firstNameResult->getTable('magenest_giftregistry')], 'main_table.giftregistry_id = registry.gift_id')->where("type=?", $type);
            $lastNameResult->getSelect()
                ->join(['registry' => $lastNameResult->getTable('magenest_giftregistry')], 'main_table.giftregistry_id = registry.gift_id')->where("type=?", $type);
        }

        $finalResult = [];

        if ($lastNameResult) {
            foreach ($lastNameResult->getData() as $lastResult) {
                foreach ($firstNameResult->getData() as $firstResult) {
                    if ($firstResult == $lastResult) {
                        array_push($finalResult, $lastResult);
                        break;
                    }
                }
            }
        }

        return $finalResult;
    }

    /**
     * @param $registryId
     * @return mixed
     */
    public function getInforEvent($registryId)
    {
        $infor = $this->_eventFactory->create()
            ->addFieldToFilter('gift_id', $registryId)
            ->addFieldToFilter('show_in_search', 'on')->getFirstItem();
        if ($infor) {
            return $infor;
        }
        return null;
    }

    /**
     * @param $event
     * @return string
     */
    public function getViewUrl($event_password, $event_id, $event_type)
    {
        if ($event_password != null) {
            return $this->_url->getUrl('gift_registry/guest/view/', ['id' => $event_id ,'pass'=>$event_password,'type'=>$event_type]);
        } else {
            return $this->_url->getUrl('gift_registry/guest/view/', ['id' => $event_id ,'type'=>$event_type]);
        }
    }
}
