<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_GiftRegistry extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_GiftRegistry
 */

namespace Magenest\GiftRegistry\Model\Api;

use Magenest\GiftRegistry\Api\ShippingRegistryInterface;
use Magenest\GiftRegistry\Helper\Cart;
use Magento\Framework\Json\Helper\Data;
use Magento\Quote\Model\Quote;

/**
 * Class ShippingRegistry
 * @package Magenest\GiftRegistry\Model\Api
 */
class ShippingRegistry implements ShippingRegistryInterface
{
    /**
     * @var Cart
     */
    protected $_quoteHelper;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * ShippingRegistry constructor.
     * @param Cart $cartHelper
     * @param Data $jsonHelper
     */
    public function __construct(
        Cart $cartHelper,
        Data $jsonHelper
    ) {
        $this->_quoteHelper = $cartHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return string
     */
    public function request()
    {
        $registryInCart = [
            'is_for_registry' => '',
            'registryId' => '',
            'registryAddress' => '',
            'registryAddressId' => ''
        ];
        $is_for_registry = $this->_quoteHelper->isForGiftRegistry();
        $registryId = $this->_quoteHelper->getRegistryId();
        $customerAddress = $this->_quoteHelper->getRegistryAddress();

        $registryAddress = [
            'firstname' => $customerAddress->getFirstname(),
            'lastname' => $customerAddress->getLastname(),
            'telephone' => $customerAddress->getTelephone(),
            'street' => $customerAddress->getStreet(),
            'postcode' => $customerAddress->getPostcode(),
            'city' => $customerAddress->getCity(),
            'region' => $customerAddress->getRegion(),
            'regionId' => (string)$customerAddress->getRegionId(),
            'regionCode' => $customerAddress->getRegionCode(),
            'country' => $customerAddress->getCountry(),
            'countryId' => $customerAddress->getCountryId(),
            'customerAddressId' => $customerAddress->getId(),
            'customerId' => $customerAddress->getCustomerId(),
        ];

        $registryInCart['is_for_registry'] = $is_for_registry;
        $registryInCart['registryId'] = $registryId;
        $registryInCart['registryAddressId'] = $customerAddress->getId();
        $registryInCart['registryAddress'] = $registryAddress;

        $result = [
            'success' => true,
            'registryInCart' => $registryInCart
        ];
        return $this->jsonHelper->jsonEncode($result);
    }
}
