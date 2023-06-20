<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:05
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit;

/**
 * Class Tabs
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit
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
