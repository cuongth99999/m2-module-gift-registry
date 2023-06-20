<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 11:32
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab;

use Magenest\GiftRegistry\Model\Status;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Main
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Status $status
     * @param TypeFactory $typeFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Status $status,
        TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_typeFactory = $typeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('type');

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }

        $fieldset->addField(
            'event_title',
            'text',
            [
                'name' => 'event_title',
                'label' => __('Event Name'),
                'title' => __('Event Name'),
                'required' => true,
                'maxlength' => 100,
            ]
        );
        $event_type = $model->getData('event_type');
        if ($event_type != '') {
            $fieldset->addField(
                'event_type',
                'text',
                [
                    'name' => 'event_type',
                    'label' => __('Event Code'),
                    'title' => __('Event Code'),
                    'readonly' => true,
                    'required' => true,
                ]
            );
        } else {
            $fieldset->addField(
                'event_type',
                'text',
                [
                    'name' => 'event_type',
                    'label' => __('Event Code'),
                    'title' => __('Event Code'),
                    'required' => true,
                    'class' => 'letters-only'
                ]
            );
        }
        $fieldset->addField(
            'description',
            'textarea',
            [
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'description',
                'cols' => 20,
                'rows' => 5,
                'wrap' => 'soft'
            ]
        );

        // Setting custom renderer for content field to remove label column
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $this->_status->getOptionArray(),
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Event Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Event Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
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
