<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 13:41
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit;

/**
 * Class Tabs
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('post_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }
}
