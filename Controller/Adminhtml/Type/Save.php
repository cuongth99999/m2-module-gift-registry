<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 16:15
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class Save extends Type
{
    /**
     * @var Json
     */
    protected $_json;

    /**
     * Save constructor.
     * @param TypeFactory $typeFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Type $typeResource
     * @param CollectionFactory $typeCollectionFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $giftRegistryCollection
     * @param LoggerInterface $logger
     * @param UploaderFactory $fileUploaderFactory
     * @param Image $imageModel
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Filter $filter
     * @param Context $context
     * @param TypeListInterface $typeList
     * @param Json $json
     */
    public function __construct(
        TypeFactory $typeFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\Type $typeResource,
        CollectionFactory $typeCollectionFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory $giftRegistryCollection,
        LoggerInterface $logger,
        UploaderFactory $fileUploaderFactory,
        Image $imageModel,
        PageFactory $resultPageFactory,
        Registry $registry,
        Filter $filter,
        Context $context,
        TypeListInterface $typeList,
        Json $json
    ) {
        parent::__construct($typeFactory, $typeResource, $typeCollectionFactory, $giftRegistryCollection, $logger, $fileUploaderFactory, $imageModel, $resultPageFactory, $registry, $filter, $context, $typeList);
        $this->_json = $json;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_typeFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_typeResource->load($model, $id);
                if ($id != $model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Wrong mapping rule.'));
                }
            }
            $fields = ['general_info','registrant_info','privacy','shipping_address_info'];
            if (isset($data['field'])&&$data['field']!='') {
                $field = array_diff($data['field'], $fields);
                $fields = array_merge($field, $fields);
            }
            $data['fields'] = $this->_json->serialize($fields);

            if (isset($data['additions_field'])&&$data['additions_field']!='') {
                $data['additions_field'] = $this->_json->serialize($data['additions_field']);
            } else {
                $data['additions_field'] = $this->_json->serialize([]);
            }
            if ($data['event_type'] == null || $data['event_type'] == '') {
                $data['event_type'] = $this->genEventType();
            }
            $data['event_type'] = $this->convertName($data['event_type']);
            $model->addData($data);
            $this->_session->setPageData($model->getData());
            $imageName = $this->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            $model->setImage($imageName);
            $thumbnail = $this->uploadFileAndGetName('thumnail', $this->imageModel->getBaseDir(), $data);
            $model->setThumnail($thumbnail);

            try {
                if (!$id && $this->checkTypeCode($data['event_type'])) {
                    throw new LocalizedException(__('The value specified in the Event Type field already exists.'));
                }
                $this->_typeResource->save($model);
                if ($model->getId()) {
                    $this->messageManager->addSuccessMessage(__('The event type has been saved.'));
                }
                $this->_session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('*/*/');
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the mapping.'));
                $this->_logger->critical($e);
                $this->_session->setPageData($data);
                $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect;
    }

    /**
     * @param $code
     * @return bool
     */
    public function checkTypeCode($code)
    {
        $collection = $this->_typeCollectionFactory->create()->addFieldToFilter('event_type', $code)->getFirstItem();
        if ($collection->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $title
     * @return string
     */
    public function genEventType($title = '')
    {
        $number = $this->generateNum();
        $event_type = strtolower(trim($title));
        $event_type = str_replace(" ", "-", $event_type) . "-" . $number;
        if ($this->checkTypeCode($number)) {
            $this->genEventType($number);
        }
        return $number;
    }

    /**
     * @return string
     */
    public function generateNum()
    {
        $length = 5;
        $c = "0123456789";
        $rand = '';
        for ($i = 0; $i < $length; $i ++) {
            $rand .= $c [rand() % strlen($c)];
        }
        return $rand;
    }

    /**
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return mixed|string
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

    /**
     * @param $str
     * @return string|string[]|null
     */
    public function convertName($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);

        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'a', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'e', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'i', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'o', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'u', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);

        return $str;
    }
}
