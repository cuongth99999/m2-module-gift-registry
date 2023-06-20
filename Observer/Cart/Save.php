<?php
namespace Magenest\GiftRegistry\Observer\Cart;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Save
 * @package Magenest\GiftRegistry\Observer\Cart
 */
class Save implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $itemGiftRegistryCollection;

    /**
     * Save constructor.
     * @param CollectionFactory $itemGiftRegistryCollection
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $itemGiftRegistryCollection,
        Data $helperData
    ) {
        $this->itemGiftRegistryCollection = $itemGiftRegistryCollection;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Cart $cart */
        $cart = $observer->getEvent()->getData('cart');
        $cartItems = $cart->getItems();

        $flag= true;
        $has_gritem = $this->hasGiftRegistryItem($cartItems);
        /** @var Item $cartItem */
        foreach ($cartItems as $cartItem) {
            $buyRequest = $cartItem->getBuyRequest()->toArray();
            if (!isset($buyRequest['giftregistry'])&&$has_gritem) {
                $flag = false;
            } elseif (isset($buyRequest['giftregistry'])) {
                $flag = $this->checkGiftRegistryItemInCart($cartItem);
                if (!$flag) {
                    break;
                }
            }
            if (!$flag) {
                $cart->removeItem($cartItem->getItemId());
                throw new \Exception(__("Make sure that the item is Gift Registry items"));
            }
        }
    }

    /**
     * @param $cartItems
     * @return bool
     * @throws \Exception
     */
    public function hasGiftRegistryItem($cartItems)
    {
        if (!empty($cartItems)&&!is_array($cartItems->getItems())) {
            throw new \Exception(__('We can\'t add this item to your shopping cart right now.'));
        }
        $flag = false;
        /** @var Item $cartItem */
        foreach ($cartItems as $cartItem) {
            $buyRequest = $cartItem->getBuyRequest()->toArray();
            if (isset($buyRequest['giftregistry'])) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    /**
     * @param $cartItem
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkGiftRegistryItemInCart($cartItem)
    {
        $flag = true;
        /** @var Item $cartItem */
        $buyRequest = $cartItem->getBuyRequest()->toArray();
        if (isset($buyRequest['giftregistry'])) {
            $qtyMode = $this->helperData->getDisplayQtyMode();
            if (($qtyMode == "0" || $qtyMode == null)&&isset($buyRequest['item'])) {
                $productId = $cartItem->getProduct()->getId();
                $giftId = $buyRequest['giftregistry'];
                $itemModel = $this->itemGiftRegistryCollection->create()
                    ->addFieldToFilter('gift_id', $giftId)
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
                if ($itemModel->getId()&&$itemModel->getId() == $buyRequest['item']) {
                    $desiredQty = $itemModel->getQty();
                    $qtyInCart = $cartItem->getQty();
                    if ($desiredQty < $qtyInCart) {
                        $flag = false;
                    } else {
                        $flag = true;
                    }
                }
            } else {
                $flag = true;
            }
        }
        return $flag;
    }
}
