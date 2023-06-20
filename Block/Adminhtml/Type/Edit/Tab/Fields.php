<?php
namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Fields
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab
 */
class Fields extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * Fields constructor.
     * @param Fieldset $rendererFieldset
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Fieldset $rendererFieldset,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Json $json,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_rendererFieldset->setData('field-section', $this);
        $this->_json = $json;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Fields
     * @throws LocalizedException
     */
    public function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('type');
        $form = $this->_formFactory->create();
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magenest_GiftRegistry::eventtype/fieldsection.phtml'
        )->setNewChildUrl(
            $this->getUrl('sales_rule/promo_quote/newConditionHtml/form/rule_conditions_fieldset')
        );
        $form->addFieldset(
            'fields',
            [
                'legend' => ''
            ]
        )->setRenderer(
            $renderer
        );
        $data = $model->getData();
        if ($this->getRequest()->getParam('id')) {
            if (isset($data['fields']) && $data['fields'] != "") {
                $data['fields'] = $this->_json->unserialize($data['fields']);
            }
        } else {
            $data['fields'] = [];
        }
        $renderer->setData('event_type', $data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Sections');
    }

    /**
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return __('Sections');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return bool|string
     */
    public function getAboutEvent()
    {
        $typeModel = $this->_coreRegistry->registry('type');

        $this->jsLayout['components']['giftregistry_aboutevent']['component'] = "Magenest_GiftRegistry/js/eventtype/aboutevent";
        $this->jsLayout['components']['giftregistry_aboutevent']['template']  = "Magenest_GiftRegistry/eventtype/aboutevent";
        if ($typeModel->getData('additions_field')) {
            $aboutevent = $this->_json->unserialize($typeModel->getData('additions_field'));
            $this->jsLayout['components']['giftregistry_aboutevent']['config']['about_event'] = $aboutevent;
        } else {
            $this->jsLayout['components']['giftregistry_aboutevent']['config']['about_event'] = null;
        }
        return $this->_json->serialize($this->jsLayout);
    }
}
