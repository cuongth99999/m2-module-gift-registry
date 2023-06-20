<?php

namespace Magenest\GiftRegistry\Observer\Cart;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ValidateAddToCart
 * @package Magenest\GiftRegistry\Observer\Cart
 */
class ValidateAddToCart implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * ValidateAddToCart constructor.
     * @param Data $data
     * @param Session $session
     */
    public function __construct(
        Data $data,
        Session $session
    ) {
        $this->data = $data;
        $this->_session = $session;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $quote = $this->_session->getQuote();
        if (!$quote->getIsValidateExpired()) {
            $quote->setData('is_validate_expired', true);
            $items = $quote->getAllItems();
            $isExpired = $this->data->isQuoteContainExpiredEventItem($items);
            if ($isExpired) {
                throw new LocalizedException(__("Event has expired, please clear your cart before place an another order."));
            }
        }
    }
}
