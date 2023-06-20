<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 12:00
 */

namespace Magenest\GiftRegistry\Controller\Guest;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\Theme\Image;
use Magenest\GiftRegistry\Model\TypeFactory as TypeFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 * @package Magenest\GiftRegistry\Controller\Guest
 */
class View extends AbstractAction
{
    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /** @var GiftRegistryFactory $registryFactory */
    protected $registryFactory;

    /** @var GiftRegistry $registryResource */
    protected $registryResource;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /** @var TypeFactory $_typeFactory */
    protected $_typeFactory;

    /**
     * @var Type
     */
    protected $_typeResource;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var Image
     */
    protected $_imageModel;

    /**
     * View constructor.
     *
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param GiftRegistryFactory $registryFactory
     * @param GiftRegistry $registryResource
     * @param CollectionFactory $itemFactory
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Repository $assetRepo
     * @param Data $helperData
     * @param UrlInterface $urlInterface
     * @param Image $imageModel
     */
    public function __construct(
        TypeFactory $typeFactory,
        Type $typeResource,
        GiftRegistryFactory $registryFactory,
        GiftRegistry $registryResource,
        CollectionFactory $itemFactory,
        Context $context,
        CurrentCustomer $currentCustomer,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Repository $assetRepo,
        Data $helperData,
        UrlInterface $urlInterface,
        Image $imageModel
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->registryResource = $registryResource;
        $this->_currentCustomer = $currentCustomer;
        $this->registryFactory = $registryFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_itemFactory = $itemFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->assetRepo = $assetRepo;
        $this->helperData = $helperData;
        $this->_urlInterface = $urlInterface;
        $this->_imageModel = $imageModel;
        parent::__construct($context, $helperData);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $giftRegistryId = isset($params['gift']) ? $params['gift'] : null;
        if ($giftRegistryId != null) {
            $pathInfo = $this->getRequest()->getPathInfo();
            if (strpos($pathInfo, 'gift_registry/guest/view/gift') !== false) {
                $resultPage = $this->resultRedirectFactory->create();
                if (isset($params[$giftRegistryId])) {
                    $resultPage->setPath("gift-registry/view/gift/" . $giftRegistryId . "/" . $params[$giftRegistryId]);
                } else {
                    $resultPage->setPath("gift_registry/index/listgift");
                }
                return $resultPage;
            }
            $giftregistryModel = $this->registryFactory->create();
            $this->registryResource->load($giftregistryModel, $giftRegistryId);
            $metaDescription = preg_replace("/[\r\n]{2,}/", " ", html_entity_decode(trim(strip_tags(html_entity_decode($giftregistryModel->getData("description") ?? '')))));
            $image = $giftregistryModel->getData("image_joinus") ? $this->_imageModel->getBaseUrl() . $giftregistryModel->getData("image_joinus") : $this->helperData->getViewFileUrl('/') . '/Magenest_GiftRegistry/images/guest-view/joinus-default.jpg';
            if (!$giftregistryModel->getId()) {
                $resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/index/listgift");
                $this->messageManager->addNoticeMessage(__("This event has been deleted! Please contact the producer!"));
                return $resultPage;
            }
            if ($giftregistryModel->getData('is_expired')) {
                $resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/index/listgift");
                $this->messageManager->addNoticeMessage(__("This event has expired! Please contact the producer!"));
                return $resultPage;
            }
            $type = $giftregistryModel->getData('type');
            $typeModel = $this->_typeFactory->create();
            $this->_typeResource->load($typeModel, $type, 'event_type');
            if ($typeModel->getData('status') == 2) {
                $resultPage = $this->resultRedirectFactory->create()->setPath("gift_registry/index/listgift");
                $this->messageManager->addNoticeMessage(__("This event has been disabled! Please contact the producer!"));
                return $resultPage;
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($giftregistryModel->getTitle()));
            $resultPage->getConfig()->setDescription(substr($metaDescription, 0, 100) . '...');
            $resultPage->getConfig()->setMetadata('og:title', $giftregistryModel->getTitle());
            $resultPage->getConfig()->setMetadata('og:type', "website");
            $resultPage->getConfig()->setMetadata('og:url', $this->_urlInterface->getCurrentUrl());
            $resultPage->getConfig()->setMetadata('og:image', $image);
            return $resultPage;
        } else {
            $resultPage = $this->resultRedirectFactory->create();
            $resultPage->setPath("gift_registry/index/listgift");
            return $resultPage;
        }
    }
}
