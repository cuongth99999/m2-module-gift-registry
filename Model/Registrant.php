<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Registrant as ResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Registrant
 * @package Magenest\GiftRegistry\Model
 */
class Registrant extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry_registrant';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftregistry_owner';

    /**
     * Registrant constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel $resource
     * @param ResourceModel\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Registrant $resource,
        \Magenest\GiftRegistry\Model\ResourceModel\Registrant\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
