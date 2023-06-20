<?php
namespace Magenest\GiftRegistry\Plugin\Checkout;

use Magenest\GiftRegistry\Helper\Cart;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\Tran;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class ShippingInformationManagement
 * @package Magenest\GiftRegistry\Plugin\Checkout
 */
class ShippingInformationManagement
{
    /** @var Data  */
    protected $_helperData;

    /** @var Cart  */
    protected $_helperCart;

    /** @var TranFactory  */
    protected $_tranFactory;

    /** @var Tran  */
    protected $_tranResource;

    /** @var CartRepositoryInterface  */
    protected $_quoteRepository;

    /** @var LoggerInterface  */
    protected $_logger;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param Data $helperData
     * @param Cart $helperCart
     * @param TranFactory $tranFactory
     * @param Tran $tranResource
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        Cart $helperCart,
        TranFactory $tranFactory,
        Tran $tranResource,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->_helperData = $helperData;
        $this->_helperCart = $helperCart;
        $this->_tranFactory = $tranFactory;
        $this->_tranResource = $tranResource;
        $this->_quoteRepository = $quoteRepository;
        $this->_logger = $logger;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return mixed
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $address = $addressInformation->getShippingAddress();
        $giftregistryId = $this->_helperCart->getRegistryId();
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface && $giftregistryId != 0) {
            try {
                $extAttributes = $address->getExtensionAttributes();
                $giftregistryMessage = $extAttributes->getGiftregistryMessage();
                $giftRegistryCheckbox = $extAttributes->getGiftregistryCheckbox();
                $giftRegistrySender = $extAttributes->getGiftregistrySender();

                /** @var Quote $quote */
                $quote = $this->_quoteRepository->getActive($cartId);
                $quoteId = $quote->getId();
                $tranModel = $this->_tranFactory->create();
                $this->_tranResource->load($tranModel, $quoteId, 'quote_id');
                $tranModel->addData([
                    'giftregistry_id' => $giftregistryId,
                    'message' => $giftregistryMessage,
                    'sender' => $giftRegistrySender,
                    'quote_id' => $quoteId,
                    'incognito' => $giftRegistryCheckbox
                ]);
                $this->_tranResource->save($tranModel);
            } catch (\Exception $exception) {
                $this->_logger->critical($exception->getMessage());
            }
        }
        return $proceed($cartId, $addressInformation);
    }
}
