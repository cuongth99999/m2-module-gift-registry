<?php
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magenest\GiftRegistry\Model\ResourceModel\Type\CollectionFactory;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
abstract class Type extends Action
{
    /** @var TypeFactory $_typeFactory */
    protected $_typeFactory;

    /** @var \Magenest\GiftRegistry\Model\ResourceModel\Type $_typeResource */
    protected $_typeResource;

    /**
     * @var CollectionFactory
     */
    protected $_typeCollectionFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory
     */
    protected $_giftRegistryCollection;

    /** @var LoggerInterface $_logger */
    protected $_logger;

    /** @var UploaderFactory $_fileUploaderFactory */
    protected $_fileUploaderFactory;

    /** @var Image $imageModel */
    protected $imageModel;

    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @var Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var  Filter $_filer */
    protected $_filer;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * Type constructor.
     *
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
        TypeListInterface $typeList
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->_typeCollectionFactory = $typeCollectionFactory;
        $this->_giftRegistryCollection = $giftRegistryCollection;
        $this->_logger = $logger;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->imageModel = $imageModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_filer = $filter;
        $this->typeList = $typeList;
        parent::__construct($context);
    }

    /**
     * @param $code
     * @return bool
     */
    public function checkGiftRegistry($code)
    {
        $collection = $this->_giftRegistryCollection->create()->addFieldToFilter('type', $code)->getFirstItem();
        if ($collection->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::type');
    }
}
