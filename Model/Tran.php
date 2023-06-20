<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 08:58
 */
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\ResourceModel\Tran as ResourceTran;
use Magenest\GiftRegistry\Model\ResourceModel\Tran\Collection as Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Tran
 * @package Magenest\GiftRegistry\Model
 */
class Tran extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry_order';

    /**
     * @var string
     */
    protected $_eventPrefix = 'tran';

    /**
     * Tran constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceTran $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceTran $resource,
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
