<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/5/17
 * Time: 12:46 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Upload
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Upload extends AbstractAction
{
    /**
     * @var GiftRegistryFactory
     */
    protected $_eventFactory;

    /**
     * @var GiftRegistry
     */
    protected $_eventResource;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var Image
     */
    protected $imageModel;

    /**
     * Upload constructor.
     * @param LoggerInterface $logger
     * @param UploaderFactory $fileUploaderFactory
     * @param Image $imageModel
     * @param GiftRegistryFactory $eventFactory
     * @param GiftRegistry $eventResource
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        LoggerInterface $logger,
        UploaderFactory $fileUploaderFactory,
        Image $imageModel,
        GiftRegistryFactory $eventFactory,
        GiftRegistry $eventResource,
        Context $context,
        Data $data
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->imageModel = $imageModel;
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        $this->_eventResource = $eventResource;
        return parent::__construct($context, $data);
    }

    /**
     * @return Redirect
     * @throws FileSystemException
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getPostValue();
        $file = $this->getRequest()->getFiles('image');
        if (empty($file)) {
            $this->messageManager->addErrorMessage(__('Something went wrong while upload the image!'));
            return $this->resultRedirectFactory->create()->setPath('gift_registry/index/manageregistry/', ['type'=>$params['registry_type'],'event_id'=> $params['registry_id']]);
        }
        $this->_logger->debug(print_r($file, true));
        $this->_logger->debug(print_r($params, true));
        $image = $this->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $file);
        $registry = $this->_eventFactory->create();
        $this->_eventResource->load($registry, $params['registry_id']);
        $image_type = $params['image_type'];
        if ($image_type == 'change-location') {
            $registry->setImageLocation($image);
        } elseif ($image_type == 'change-joinus') {
            $registry->setImageJoinus($image);
        } else {
            $registry->setImage($image);
        }
        $this->_eventResource->save($registry);
        $this->messageManager->addSuccessMessage(__('The image upload successfully!'));
        return $this->resultRedirectFactory->create()->setPath('/index/manageregistry/', ['type'=>$params['registry_type'],'event_id'=> $params['registry_id']]);
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
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
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
