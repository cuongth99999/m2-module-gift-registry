<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 09/08/2018
 * Time: 08:18
 */

namespace Magenest\GiftRegistry\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class OrderStatus
 * @package Magenest\GiftRegistry\Model\Config
 */
class OrderStatus implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $_orderStatusCollection;

    /**
     * OrderStatus constructor.
     * @param CollectionFactory $orderStatusCollection
     */
    public function __construct(CollectionFactory $orderStatusCollection)
    {
        $this->_orderStatusCollection = $orderStatusCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_orderStatusCollection->create()->toOptionArray();
    }
}
