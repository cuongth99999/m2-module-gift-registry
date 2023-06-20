<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:30
 */
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Type as ResourceType;
use Magenest\GiftRegistry\Model\ResourceModel\Type\Collection as Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Type
 * @package Magenest\GiftRegistry\Model
 */
class Type extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry_event_type';

    /**
     * @var string
     */
    protected $_eventPrefix = 'type';

    /**
     * Type constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceType $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceType $resource,
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
        return [self::CACHE_TAG];
    }
}
