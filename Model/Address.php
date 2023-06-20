<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 01:27
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Address\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Model
 */
class Address extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry_shipping_address';
    /**
     * Address constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\Address $resource
     * @param ResourceModel\Address\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Address $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
