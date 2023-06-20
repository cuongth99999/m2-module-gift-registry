<?php

namespace Magenest\GiftRegistry\Controller\Wishlist;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Wishlist\Controller\WishlistProviderInterface;

/**
 * Class CheckProductsDetail
 * @package Magenest\GiftRegistry\Controller\Index
 */
class GetAllRegistry extends AbstractAction
{
    /**
     * @var Data $dataHelper
     */
    protected $dataHelper;

    /**
     * @var Session $_sessionModel
     */
    protected $_sessionModel;

    /**
     * @var UrlInterface $_urlInterface
     */
    protected $_urlInterface;

    /**
     * CheckProductsDetail constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Session $session
    ) {
        parent::__construct($context, $dataHelper);
        $this->dataHelper = $dataHelper;
        $this->_sessionModel = $session;
        $this->_urlInterface = $this->_url;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $params = $this->getRequest()->getParams();
        $data = [];
        if (!empty($params)) {
            $customerId = $this->_sessionModel->getCustomer()->getId();
            $giftRegistryId = $this->dataHelper->getGiftId();
            $haveOneRegistry = $this->dataHelper->getHaveOneRegistry();
            $haveExpiredRegistry = $this->dataHelper->isHaveUnexpiredGift($customerId);
            $url =  $this->_urlInterface->getUrl('gift_registry/wishlist/addalltogiftregistry');

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
                        'giftData' => $giftData,
                        'data' => [
                            'qty' => $this->getRequest()->getParam('qty'),
                            'giftregistry' => ''
                        ]
                    ];
                } else {
                    $data = [
                        'showGift' => false,
                        'urlAdd' => $url,
                        'data' => [
                            'qty' => $this->getRequest()->getParam('qty'),
                            'giftregistry' => $giftRegistryId
                        ]
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
