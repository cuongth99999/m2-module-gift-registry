<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:05
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Form
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit
 */
class Form extends Generic
{
    /**
     * Prepare form
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
