<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 24/12/2015
 * Time: 00:31
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Transaction;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Transaction
 * @package Magenest\GiftRegistry\Block\Adminhtml\Transaction
 */
class Transaction extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_GiftRegistry';

        parent::_construct();
        $this->removeButton('add');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Magenest\GiftRegistry\Block\Adminhtml\Transaction\Grid', 'giftregistry.transaction.grid')
        );
        return parent::_prepareLayout();
    }
}
