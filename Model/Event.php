<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Event as ResourceEvent;
use Magenest\GiftRegistry\Model\ResourceModel\Event\Collection as Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Event
 * @package Magenest\GiftRegistry\Model
 */
class Event extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_registry_event';

    /**
     * @var string
     */
    protected $_eventPrefix = 'event';

    /**
     * Event constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceEvent $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceEvent $resource,
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
