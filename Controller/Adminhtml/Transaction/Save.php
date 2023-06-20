<?php

namespace Magenest\GiftRegistry\Controller\Adminhtml\Transaction;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime as MagentoDateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Save
 * @package Magenest\RewardPoints\Controller\Customer
 */
class Save extends Action
{

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var MagentoDateTime
     */
    protected $date;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var GiftRegistryFactory
     */
    protected $registryFactory;

    /**
     * @var GiftRegistry
     */
    protected $registryResource;

    /**
     * @var RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var Registrant
     */
    protected $registrantResource;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * Save constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param MagentoDateTime $date
     * @param TimezoneInterface $timezone
     * @param GiftRegistryFactory $registryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param RegistrantFactory $registrantFactory
     * @param Registrant $registrantResource
     * @param Json $json
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        MagentoDateTime $date,
        TimezoneInterface $timezone,
        GiftRegistryFactory $registryFactory,
        GiftRegistry $giftRegistryResource,
        RegistrantFactory $registrantFactory,
        Registrant $registrantResource,
        Json $json
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_context           = $context;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->registryFactory = $registryFactory;
        $this->registryResource = $giftRegistryResource;
        $this->registrantFactory = $registrantFactory;
        $this->registrantResource = $registrantResource;
        $this->_json = $json;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     * @throws AlreadyExistsException
     */
    public function execute()
    {
        $data            = $this->getRequest()->getParams();
        $data['gift_options'] = $this->getGiftAdditionField($data);
        /** Validate Param */
        //getData
        $data['password'] = str_replace(' ', '', isset($data['password']) ? $data['password'] : "");
        $data['re_password'] = str_replace(' ', '', isset($data['re_password']) ? $data['re_password'] : "");
        $data['type'] = htmlspecialchars($data['type'], ENT_QUOTES, 'UTF-8');
        $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
        $data['description'] = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
        $data['shipping_address'] = htmlspecialchars($data['shipping_address'], ENT_QUOTES, 'UTF-8');

        $data['updated_at'] = $this->date->gmtDate('Y-m-d H:i:s');
        $data['show_in_search'] = isset($data['show_in_search']) ? $data['show_in_search'] : '';
        $success = true;

        unset($data['re_password']);
        $data['password'] = sha1($data['password']);
        unset($data['gift_id']);
        $data['is_expired'] = 0;
        //save table magenest_giftregistry
        $registryModel = $this->registryFactory->create();
        $registryModel->setData($data);
        $this->registryResource->save($registryModel);
        $giftRegistryId = $registryModel->getId();

        //save table magenest_giftregistry_registrant
        $registrantData[] = [
            'email' => $data['email'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'giftRegistry_id' => $giftRegistryId
        ];
        if (isset($registrantData) && !empty($registrantData)) {
            foreach ($registrantData as $key => $value) {
                if ($giftRegistryId > 0) {
                    $value['giftregistry_id'] = $giftRegistryId;
                    $registrantModel = $this->registrantFactory->create();
                    $registrantModel->setData($value);
                    $this->registrantResource->save($registrantModel);
                }
            }
        }
        $resultJson = $this->_resultJsonFactory->create();

        return $resultJson->setData(['success'=>$success]);
    }

    /**
     * @param $params
     * @return string
     */
    public function getGiftAdditionField($params)
    {
        $options = isset($params['field']) ? $params['field'] : [];
        return $this->_json->serialize($options);
    }
}
