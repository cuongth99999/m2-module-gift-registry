<?php

namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class ShippingAddressInformation extends Generic implements TabInterface
{
    const GROUP_NAME = 'shipping_address_information';

    protected function _prepareForm()
    {
        $registryModel = $this->_coreRegistry->registry('registry');
        $informationModel = $this->_coreRegistry->registry('information');
        $allAdditionFields = $this->_coreRegistry->registry('all_addition_fields');

        $additionFields = $allAdditionFields[self::GROUP_NAME];

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Shipping Address Information')]);

        $fieldset->addField(
            'shipping_address',
            'text',
            [
                'name' => 'shipping_address',
                'label' => __('Shipping Address'),
                'title' => __('Shipping Address'),
                'disabled' => true,
                'required' => false,
                'value' => ''
            ]
        );

        if(!empty($additionFields)){
            foreach ($additionFields as $field)
            {
                $fieldset->addField(
                    $field['type'] . '_' . $field['id'],
                    'text',
                    [
                        'name' => 'field_' . $field['id'],
                        'label' => $field['label'],
                        'title' => $field['label'],
                        'disabled' => true,
                        'required' => false,
                        'value' => ''
                    ]
                );
            }
        }

        if ($informationModel->getId()) {
            $data = array_merge($informationModel->getData(), $registryModel->getData());
            $form->setValues($data);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Shipping Address Information');
    }

    public function getTabTitle()
    {
        return __('Shipping Address Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
