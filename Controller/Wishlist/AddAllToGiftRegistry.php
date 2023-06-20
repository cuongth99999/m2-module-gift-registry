<?php

namespace Magenest\GiftRegistry\Controller\Wishlist;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ItemCarrier;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class AddAllToGiftRegistry extends AbstractAction implements HttpPostActionInterface
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var WishlistHelper
     */
    protected $helper;

    /**
     * @var ItemCarrier
     */
    protected $itemCarrier;

    /**
     * AddAllToGiftRegistry constructor.
     * @param Context $context
     * @param Data $data
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param WishlistHelper $helper
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        Data $data,
        WishlistProviderInterface $wishlistProvider,
        Validator $formKeyValidator,
        WishlistHelper $helper,
        ItemCarrier $itemCarrier
    ){
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context, $data);
    }

    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $wishlist = $this->wishlistProvider->getWishlist();

        if (!$wishlist) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->itemCarrier->moveAllToGiftRegistry($wishlist, $this->getRequest()->getParam('qty'), $this->getRequest()->getParam('giftregistry'));
        $resultRedirect->setUrl($this->helper->getListUrl($wishlist->getId()));
        return $resultRedirect;
    }
}
