<?php
namespace Magenest\GiftRegistry\Block\Adminhtml\Edit\Tab;

use Magenest\GiftRegistry\Block\Adminhtml\Grid;

/**
 * Class Transaction
 * @package Magenest\RewardPoints\Block\Adminhtml\Edit\Tab
 */
class Transaction extends Grid
{

    /**
     * @return $this|Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftregistry/index/transactionHistory', ['_current' => true]);
    }
}
