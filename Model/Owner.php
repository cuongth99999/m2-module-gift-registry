<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Owner as ResourceModel;
use Magenest\GiftRegistry\Model\ResourceModel\Owner\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Owner
 * @package Magenest\GiftRegistry\Model
 */
class Owner extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_registry_owner';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftregistry_owner';

    /**
     * Owner constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel $resource,
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
