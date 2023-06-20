<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Class CheckProductsDetail
 * @package Magenest\GiftRegistry\Controller\Index
 */
class CheckProductsDetail extends AbstractAction
{
    protected $resultPageFactory;

    protected $_collectionFactory;

    /** @var ProductRepositoryInterface $productRepository */
    protected $productRepository;

    /** @var Data $dataHelper */
    protected $dataHelper;

    /** @var UrlInterface $_urlInterface */
    protected $_urlInterface;

    /** @var Session $_sessionModel */
    protected $_sessionModel;

    /**
     * CheckProductsDetail constructor.
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param Data $dataHelper
     * @param Session $session
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Data $dataHelper,
        Session $session
    ) {
        parent::__construct($context, $dataHelper);
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->_urlInterface = $this->_url;
        $this->_sessionModel = $session;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $data = [];
        if (!empty($params)) {
            $customerId = $this->_sessionModel->getCustomer()->getId();
            $productId = $params['productId'];
            $giftRegistryId = $this->dataHelper->getGiftId();
            $haveOneRegistry = $this->dataHelper->getHaveOneRegistry();
            $haveExpiredRegistry = $this->dataHelper->isHaveUnexpiredGift($customerId);
            $url =  $this->_urlInterface->getUrl('gift_registry/index/add', ['product' => $productId, 'giftregistry' => $giftRegistryId]);
            if (!$haveOneRegistry) {
                if ($haveExpiredRegistry) { // gifts isn't actived
                    $data = [
                        'messageType' => __('All events have expired! Please create a gift registry before adding items.')
                    ];
                } else {
                    $data = [
                        'messageType' => __('Customer have to login and create gift registry before adding item to gift registry.')
                    ];
                }
            } else { //The customer has more 1 registry
                if ($haveOneRegistry > 1) {
                    $giftData = $this->dataHelper->getGiftRegistryByCustomer();
                    $data = [
                        'showGift' => true,
                        'data' => $giftData
                    ];
                } else {
                    $data = [
                        'showGift' => false,
                        'urlAdd' => $url
                    ];
                }
            }
        }
        if (empty($data)) {
            $data = [
                'messageType' => __('Whoops, looks like something went wrong. Please try again later.')
            ];
        }
        $resultJson->setData($data);
        return $resultJson;
    }
}
