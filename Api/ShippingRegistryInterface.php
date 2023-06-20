<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Payaconnect extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Payaconnect
 */

namespace Magenest\GiftRegistry\Api;

/**
 * Interface ShippingRegistryInterface
 * @package Magenest\GiftRegistry\Api
 */
interface ShippingRegistryInterface
{
    /**
     * Returns url shipping
     *
     * @api
     * @return string url shipping
     */
    public function request();
}
