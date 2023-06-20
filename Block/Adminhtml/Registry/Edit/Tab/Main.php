<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:03
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

use DateTime;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Main
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab
 */
class Main extends Generic implements TabInterface
{
    const GROUP_NAME = 'registrant_information';

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var TypeFactory
     */
    protected $_eventFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registryFactory;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param RegistrantFactory $eventFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        RegistrantFactory $eventFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_registryFactory= $eventFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $registryModel = $this->_coreRegistry->registry('registry');
        $informationModel = $this->_coreRegistry->registry('information');
        $allAdditionFields = $this->_coreRegistry->registry('all_addition_fields');

        $additionFields = $allAdditionFields[self::GROUP_NAME];

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Registrant Information')]);
        if ($registryModel->getId()) {
            $fieldset->addField(
                'registrant_id',
                'hidden',
                ['name' => 'registrant_id']
            );
        }

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => false,
                'disabled' => true,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'disabled' => true,
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'disabled' => true,
                'required' => false,
                'value' => 'abc'
            ]
        );

        $fieldset->addField(
            'created_time',
            'text',
            [
                'name' => 'created_time',
                'label' => __('Created Time'),
                'title' => __('Created Time'),
                'disabled' => true,
                'required' => false,
                'value' => 'abc'
            ]
        );
        $fieldset->addField(
            'updated_time',
            'text',
            [
                'name' => 'updated_time',
                'label' => __('Updated Time'),
                'title' => __('Updated Time'),
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

        if ($registryModel->getId()) {
            $data = array_merge($registryModel->getData(), $informationModel->getData());
            $created_time = new DateTime($data['created_time']);
            $updated_time = new DateTime($data['updated_time']);
            $data['created_time'] = $created_time->format('Y-m-d');
            $data['updated_time'] = $updated_time->format('Y-m-d');
            $form->setValues($data);
        }

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
        return __('Registrant Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Registrant Information');
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
