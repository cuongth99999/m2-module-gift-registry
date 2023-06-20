<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:45
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry;

use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Registry
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry
 */
class Registry extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_GiftRegistry';

        parent::_construct();
        $this->removeButton('add');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container|Container
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Magenest\GiftRegistry\Block\Adminhtml\Registry\Grid', 'giftregistry.registry.grid')
        );
        return parent::_prepareLayout();
    }
}
