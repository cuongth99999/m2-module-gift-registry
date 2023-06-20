<?php
namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\GiftRegistryTmpFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Preview extends AbstractAction
{
    /** @var GiftRegistryFactory $_giftregistryFactory */
    protected $_giftregistryFactory;

    /** @var GiftRegistry $_giftregistryResource */
    protected $_giftregistryResource;

    /** @var GiftRegistryTmpFactory $_giftregistryTmpFactory */
    protected $_giftregistryTmpFactory;

    /** @var GiftRegistryTmp $_giftregistryTmpResource */
    protected $_giftregistryTmpResource;

    /** @var Session $_customerSession */
    protected $_customerSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /** @var UploaderFactory $_fileUploaderFactory */
    protected $_fileUploaderFactory;

    /** @var Image $imageModel */
    protected $imageModel;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * Preview constructor.
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param GiftRegistryTmpFactory $giftRegistryTmpFactory
     * @param GiftRegistryTmp $registryTmpResource
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param UploaderFactory $uploaderFactory
     * @param Image $imageModel
     * @param Context $context
     * @param Data $data
     * @param Json $json
     * @param DateTime $date
     */
    public function __construct(
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResource,
        GiftRegistryTmpFactory $giftRegistryTmpFactory,
        GiftRegistryTmp $registryTmpResource,
        Session $customerSession,
        JsonFactory $resultJsonFactory,
        UploaderFactory $uploaderFactory,
        Image $imageModel,
        Context $context,
        Data $data,
        Json $json,
        DateTime $date
    ) {
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_giftregistryResource = $giftRegistryResource;
        $this->_giftregistryTmpFactory = $giftRegistryTmpFactory;
        $this->_giftregistryTmpResource = $registryTmpResource;
        $this->_customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_fileUploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
        $this->_json = $json;
        $this->date = $date;
        parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        try {
            $postData = $this->getRequest()->getPost();
            if (!$this->_customerSession->isLoggedIn()) {
                throw new \Exception(__('Please login to continue.'));
            }
            $data = [];
            if ($postData&&is_array($postData->getArrayCopy())) {
                $postData = $postData->getArrayCopy();
                /** Get event option */
                $data['gift_options'] = $this->getGiftAdditionField();
                $data['title'] = htmlspecialchars($postData['title'], ENT_QUOTES, 'UTF-8');
                $data['description'] = htmlspecialchars($postData['description'], ENT_QUOTES, 'UTF-8');
                $data['date'] = $this->date->date('Y-m-d H:i:s', $postData['date']);
                $data['location'] = $postData['location'];
                $data['gift_id'] = $postData['gift_id'];
                $registrant = $postData['registrant'][$data['gift_id']];
                $data['registrant_email'] = $registrant['email'];
                $data['is_expired'] = $postData['is_expired'];
                $data['shipping_address'] = $postData['shipping_address'];

                $giftregistryModel = $this->_giftregistryFactory->create();
                $this->_giftregistryResource->load($giftregistryModel, $data['gift_id']);

                if (!empty($this->getRequest()->getFiles('giftregistry-banner'))) {
                    $banner = $this->getRequest()->getFiles('giftregistry-banner');
                    $imageBanner = $this->uploadFileAndGetName('giftregistry-banner', $this->imageModel->getBaseDir(), $banner);
                    if ($imageBanner != "") {
                        $data['image'] = $imageBanner;
                    } else {
                        $data['image'] = $giftregistryModel->getData('image');
                    }
                }
                if (!empty($this->getRequest()->getFiles('giftregistry-location'))) {
                    $location = $this->getRequest()->getFiles('giftregistry-location');
                    $imageLocation = $this->uploadFileAndGetName('giftregistry-location', $this->imageModel->getBaseDir(), $location);
                    if ($imageLocation != "") {
                        $data['image_location'] = $imageLocation;
                    } else {
                        $data['image_location'] = $giftregistryModel->getData('image_location');
                    }
                }
                if (!empty($this->getRequest()->getFiles('giftregistry-joinus'))) {
                    $joinus = $this->getRequest()->getFiles('giftregistry-joinus');
                    $imageJoinUs = $this->uploadFileAndGetName('giftregistry-joinus', $this->imageModel->getBaseDir(), $joinus);
                    if ($imageJoinUs != "") {
                        $data['image_joinus'] = $imageJoinUs;
                    } else {
                        $data['image_joinus'] = $giftregistryModel->getData('image_joinus');
                    }
                }
                $giftregistryTmp = $this->_giftregistryTmpFactory->create();
                $giftregistryTmp->setData($data);
                $this->_giftregistryTmpResource->save($giftregistryTmp);
                $giftregistryTmpId = $giftregistryTmp->getGiftIdTmp();
                $redirect_url = $this->_url->getUrl("gift_registry/registry/preview", ['gift_id' => $giftregistryTmpId]);
                $result = [
                    'message' => "success",
                    'gift_tmp_id' => $giftregistryTmpId,
                    'error' => false,
                    'redirect_url' => $redirect_url
                ];
            } else {
                throw new \Exception(__("Params don't exist!"));
            }
        } catch (\Exception $exception) {
            $result = [
                'message' => $exception->getMessage(),
                'gift_tmp_id' => 0,
                'error' => true
            ];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }

    /**
     * @return bool|string
     */
    public function getGiftAdditionField()
    {
        $params = $this->getRequest()->getParams();
        $options = isset($params['field']) ? $params['field'] : [];
        return $this->_json->serialize($options);
    }

    /**
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Exception
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
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Exception($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }
}
