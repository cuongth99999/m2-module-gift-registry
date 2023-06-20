<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 13:25
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use DateTime;
use Exception;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\AddressFactory;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime as MagentoDateTime;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Class Post
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Post extends AbstractAction
{
    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @var GiftRegistryFactory $registryFactory */
    protected $registryFactory;

    /** @var GiftRegistry $registryResource */
    protected $registryResource;

    /** @var RegistrantFactory $registrantFactory */
    protected $registrantFactory;

    /** @var Registrant $registrantResource */
    protected $registrantResource;

    /** @var AddressFactory $addressFactory */
    protected $addressFactory;

    /** @var Context $_context */
    protected $_context;

    /** @var CurrentCustomer $_currentCustomer */
    protected $_currentCustomer;

    /** @var Session $_customerSession */
    protected $_customerSession;

    /** @var MagentoDateTime $date */
    protected $date;

    /** @var Data $data */
    protected $data;

    /** @var UploaderFactory $_fileUploaderFactory */
    protected $_fileUploaderFactory;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * Post constructor.
     * @param GiftRegistryFactory $registryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param RegistrantFactory $registrantFactory
     * @param Registrant $registrantResource
     * @param AddressFactory $addressFactory
     * @param Context $context
     * @param Session $session
     * @param CurrentCustomer $currentCustomer
     * @param PageFactory $resultPageFactory
     * @param MagentoDateTime $date
     * @param Data $data
     * @param UploaderFactory $fileUploaderFactory
     * @param Image $imageModel
     * @param Json $json
     */
    public function __construct(
        GiftRegistryFactory $registryFactory,
        GiftRegistry $giftRegistryResource,
        RegistrantFactory $registrantFactory,
        Registrant $registrantResource,
        AddressFactory $addressFactory,
        Context $context,
        Session $session,
        CurrentCustomer $currentCustomer,
        PageFactory $resultPageFactory,
        MagentoDateTime $date,
        Data $data,
        UploaderFactory $fileUploaderFactory,
        Image $imageModel,
        Json $json
    ) {
        $this->date = $date;
        $this->_context = $context;
        $this->_currentCustomer = $currentCustomer;
        $this->_customerSession = $session;
        $this->registryFactory = $registryFactory;
        $this->registryResource = $giftRegistryResource;
        $this->registrantFactory = $registrantFactory;
        $this->registrantResource = $registrantResource;
        $this->addressFactory = $addressFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->data =$data;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->imageModel = $imageModel;
        $this->_json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @param $params
     * @return bool|string
     */
    public function getGiftAdditionField($params)
    {
        $options = isset($params['field']) ? $params['field'] : [];
        return $this->_json->serialize($options);
    }

    /**
     * @param $data
     * @return mixed|null
     * @throws FileSystemException
     * @throws Exception
     */
    public function saveGiftRegistry($data)
    {
        /** Get event option */
        $data['gift_options'] = $this->getGiftAdditionField($data);
        /** Validate Param */
        //remove whitespace in password
        $data['date'] = $this->date->date('Y-m-d', $data['date']);
        $today = date("Y-m-d");
        if (strtotime($today) > strtotime($data['date']) && $data['is_expired'] == 0) {
            throw new Exception(__('Invalid date information.'));
        }
        $data['password'] = str_replace(' ', '', isset($data['password']) ? $data['password'] : "");
        $data['re_password'] = str_replace(' ', '', isset($data['re_password']) ? $data['re_password'] : "");
        $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
        $data['description'] = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
        $data['updated_at'] = $this->date->gmtDate('Y-m-d H:i:s');
        $data['show_in_search'] = isset($data['show_in_search']) ? $data['show_in_search'] : '';
        if (!empty($this->getRequest()->getFiles('giftregistry-banner'))) {
            $banner = $this->getRequest()->getFiles('giftregistry-banner');
            $imageBanner = $this->uploadFileAndGetName('giftregistry-banner', $this->imageModel->getBaseDir(), $banner);
            if ($imageBanner != "") {
                $data['image'] = $imageBanner;
            }
        }
        if (!empty($this->getRequest()->getFiles('giftregistry-location'))) {
            $location = $this->getRequest()->getFiles('giftregistry-location');
            $imageLocation = $this->uploadFileAndGetName('giftregistry-location', $this->imageModel->getBaseDir(), $location);
            if ($imageLocation != "") {
                $data['image_location'] = $imageLocation;
            }
        }
        if (!empty($this->getRequest()->getFiles('giftregistry-joinus'))) {
            $joinus = $this->getRequest()->getFiles('giftregistry-joinus');
            $imageJoinUs = $this->uploadFileAndGetName('giftregistry-joinus', $this->imageModel->getBaseDir(), $joinus);
            if ($imageJoinUs != "") {
                $data['image_joinus'] = $imageJoinUs;
            }
        }
        $giftRegistryId = isset($data['event_id']) ? $data['event_id'] : null;
        $registrantData = $data['registrant'];
        try {
            if ($giftRegistryId) {
                /** Update */
                if ($data['password'] != $data['re_password']) {
                    throw new Exception(__('Password and re-password does not match.'));
                }
                unset($data['re_password']);
                if ($data['password']) {
                    $data['password'] = sha1($data['password']);
                } else {
                    unset($data['password']);
                }

                unset($data['gift_id']);
                $registryModel = $this->registryFactory->create();
                $this->registryResource->load($registryModel, $giftRegistryId);
                $registryModel->addData($data);

                $password = $registryModel->getPassword();
                if ($data['privacy'] == 'private' && $password == sha1('')) {
                    throw new Exception(__('You haven\'t created a password for private mode.'));
                }
                $this->registryResource->save($registryModel);
                /** Update Registrant Information */
                if (isset($registrantData) && !empty($registrantData)) {
                    foreach ($registrantData as $key => $value) {
                        $registrantModel = $this->registrantFactory->create();
                        $this->registrantResource->load($registrantModel, $key);
                        $registrantModel->addData($value);
                        $this->registrantResource->save($registrantModel);
                    }
                }
            } else {
                /** Create new Registry */
                if ($data['password'] != $data['re_password']) {
                    throw new Exception(__('Password and re-password does not match.'));
                }
                if ($data['privacy'] == 'private' && $data['password'] == '') {
                    throw new Exception(__('Please enter password in private mode.'));
                }
                unset($data['re_password']);
                $data['password'] = sha1($data['password']);
                unset($data['gift_id']);
                $data['customer_id'] = $this->getCustomerId();
                $data['is_expired'] = 0;
                $registryModel = $this->registryFactory->create();
                $registryModel->setData($data);
                $this->registryResource->save($registryModel);
                $giftRegistryId = $registryModel->getId();
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
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $giftRegistryId;
    }

    /**
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws Exception
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new Exception($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }

    /**
     * @return Redirect|ResultInterface|Layout|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $data = $this->getRequest()->getParams();
            if (!$this->_customerSession->isLoggedIn()) {
                throw new Exception(__('Please login to continue.'));
            }
            // validate when create new event
            if (!isset($data['event_id'])) {
                if ($this->validateIsHaveUnexpired()) {
                    throw new Exception(__('You can only have one active gift registry at a time.'));
                }
            }
            $eventList = $this->data->getEventTypeList();
            //validate if event type still exist
            if (array_search($data['type'], $eventList) === false) {
                throw new Exception(__('This event type has been disabled or deleted.'));
            }
            /** Save GiftRegistry's Information */
            $giftRegistryId = $this->saveGiftRegistry($data);
            if ($giftRegistryId) {
                $this->messageManager->addSuccessMessage(__('The Gift Registry has been created successfully!'));
                $url_key = "gift-registry/manage/id/" . $giftRegistryId;
                $manageRegistryUrl = $this->_url->getUrl();
                $resultRedirect->setUrl($manageRegistryUrl . $url_key);
            }
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            if (isset($data['gift_id'])&&$data['gift_id']!="") {
                $url_key = "gift-registry/manage/id/" . $data['gift_id'];
                $manageRegistryUrl = $this->_url->getUrl();
                $resultRedirect->setUrl($manageRegistryUrl . $url_key);
            } else {
                $resultRedirect->setPath('gift_registry/customer/mygiftregistry/');
            }
        }
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    private function validateIsHaveUnexpired()
    {
        $newGiftDate = $this->getRequest()->getParam('date');
        $type = $this->getRequest()->getParam('type');
        if ($newGiftDate) {
            if ($this->data->isHaveUnexpiredGiftByDate($this->_customerSession->getCustomerId(), $type)) {
                return true;
            }
        }
        return false;
    }
}
