<?php
namespace Magenest\GiftRegistry\Observer\Cart;

use Magenest\GiftRegistry\Helper\Cart;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class UpdatePost
 * @package Magenest\GiftRegistry\Observer\Cart
 */
class UpdatePost implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var Cart
     */
    protected $_helperCart;

    /**
     * @var CollectionFactory
     */
    protected $_giftRegistryItemCollectionFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $json;

    public function __construct(
        Data $data,
        Cart $helperCart,
        CollectionFactory $collectionFactory,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->_helperData = $data;
        $this->_helperCart = $helperCart;
        $this->_giftRegistryItemCollectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $cartData = $observer->getEvent()->getData('info')->getData();
        if (!is_array($cartData)) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
        $isForGiftRegistry = $this->_helperCart->isForGiftRegistry();
        $qtyMode = $this->_helperData->getDisplayQtyMode();
        if ($isForGiftRegistry && ($qtyMode == "0" || $qtyMode == null)) {
            $giftRegistryId = $this->_helperCart->getRegistryId();
            $quote = $this->checkoutSession->getQuote();
            foreach ($cartData as $itemId => $itemInfo) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
                $item = $quote->getItemById($itemId);
                $productId = $item->getData('product_id');
                $giftItem = $this->_giftRegistryItemCollectionFactory->create()
                    ->addFieldToFilter('gift_id', $giftRegistryId)
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
                if ($giftItem->getGiftItemId()) {
                    $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : 0;
                    $desired_qty = $giftItem->getData('qty')*1;
                    if ($desired_qty < $qty) {
//                        $cartData[$itemId]['qty'] = $desired_qty;
                        throw new LocalizedException(
                            __('You can not update cart quantity more than desired quantity of the gift registry')
                        );
                    }
                }
            }
        }
    }
}
