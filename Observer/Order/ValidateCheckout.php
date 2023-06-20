<?php

namespace Magenest\GiftRegistry\Observer\Order;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ValidateCheckout
 * @package Magenest\GiftRegistry\Observer\Order
 */
class ValidateCheckout implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * ValidateCheckout constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $orderItems = $observer->getOrder()->getItems();
        $isExpired = $this->data->isQuoteContainExpiredEventItem($orderItems);
        if ($isExpired) {
            throw new LocalizedException(__("Event has expired, please clear your cart before place an another order."));
        }
    }
}
