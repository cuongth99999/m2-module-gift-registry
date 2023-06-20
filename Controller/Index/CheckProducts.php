<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 22/01/2019
 * Time: 11:37
 */
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Class CheckProducts
 * @package Magenest\GiftRegistry\Controller\Index
 */
class CheckProducts extends AbstractAction
{
    const ACTION_STAY_PRODUCT_LIST = 1;
    const ACTION_REDIRECT_PRODUCT_DETAIL = 2;
    const ACTION_REDIRECT_TO_ADD = 3;

    /**
     * @var
     */
    protected $resultPageFactory;

    /**
     * @var
     */
    protected $_collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Data $dataHelper,
        SessionFactory $sessionFactory
    ) {
        parent::__construct($context, $dataHelper);
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->_urlInterface = $this->_url;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $customer = $this->sessionFactory->create();
        $customerId = $customer->getCustomer()->getId();

        $productId = $this->getRequest()->getParam('productId');
        $product = $this->productRepository->getById($productId);
        $productUrl = $product->getProductUrl();
        $productType = $product->getTypeId();

        $giftRegistryId = $this->dataHelper->getGiftId();

        $haveOneRegistry = $this->dataHelper->getHaveOneRegistry();
        $haveExpiredRegistry = $this->dataHelper->isHaveUnexpiredGift($customerId);
        $url =  $this->_urlInterface->getUrl('gift_registry/index/add', ['product' => $productId, 'gift_registry' => $giftRegistryId]);
        if (!$haveOneRegistry) {
            if ($haveExpiredRegistry) { // gifts isn't actived
                $this->messageManager->addErrorMessage(__('All events have expired! Please create a gift registry before adding items.'));
            } else {
                $this->messageManager->addErrorMessage(__('Customer have to login and create gift registry before adding item to gift registry.'));
            }
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        } else { //The customer has more 1 registry
            if ($haveOneRegistry > 1) {
                $this->messageManager->addWarningMessage(__('Your must choose what registry need to add item!'));
                $resultRedirect->setUrl($productUrl);
                return $resultRedirect;
            } else {
                if ($productType == 'configurable') {
                    $this->messageManager->addWarningMessage(__('Your must choose option need to add item!'));
                    $resultRedirect->setUrl($productUrl);
                    return $resultRedirect;
                } elseif ($productType == 'simple' || $productType == 'virtual') {
                    if ($product->getRequiredOptions()) {
                        $this->messageManager->addWarningMessage(__('Your must choose option need to add item!'));
                        $resultRedirect->setUrl($productUrl);
                        return $resultRedirect;
                    } else {
                        $this->messageManager->addSuccessMessage(__('The item is added to your gift registry.'));
                        $resultRedirect->setUrl($url);
                        return $resultRedirect;
                    }
                }
            }
        }
        return $resultJson;
    }
}
