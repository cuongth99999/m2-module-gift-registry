<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 13:38
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Form
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit
 */
class Form extends Generic
{
    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype' => 'multipart/form-data'
            ]]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
