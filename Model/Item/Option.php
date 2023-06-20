<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 22/02/2016
 * Time: 00:46
 */
namespace Magenest\GiftRegistry\Model\Item;

use Magenest\GiftRegistry\Model\ResourceModel\Item\Option\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Option
 * @package Magenest\GiftRegistry\Model\Item
 */
class Option extends \Magento\Framework\Model\AbstractModel implements \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{

    /**
     * Option constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\Option $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\Option $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve value associated with this option
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->_getData('value');
        return $value;
    }
}
