<?php

namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class AdditionInformation extends Generic implements TabInterface
{
    const GROUP_NAME = 'additional_section';

    protected function _prepareForm()
    {
        $registryModel = $this->_coreRegistry->registry('registry');
        $informationModel = $this->_coreRegistry->registry('information');
        $allAdditionFields = $this->_coreRegistry->registry('all_addition_fields');

        $additionFields = $allAdditionFields[self::GROUP_NAME];

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Addition Information')]);

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
        return __('Addition Information');
    }

    public function getTabTitle()
    {
        return __('Addition Information');
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
