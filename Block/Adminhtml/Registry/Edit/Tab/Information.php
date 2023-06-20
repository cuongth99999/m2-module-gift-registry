<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 26/04/2016
 * Time: 15:01
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab;

use DateTime;
use Magenest\GiftRegistry\Model\ItemFactory;
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
 * Class Information
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry\Edit\Tab
 */
class Information extends Generic implements TabInterface
{
    const GROUP_NAME = 'general_information';

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var TypeFactory
     */
    protected $_itemFactory;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param ItemFactory $itemFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        ItemFactory $itemFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_itemFactory = $itemFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _prepareForm()
    {
        $registryModel = $this->_coreRegistry->registry('registry');
        $informationModel = $this->_coreRegistry->registry('information');
        $allAdditionFields = $this->_coreRegistry->registry('all_addition_fields');

        $additionFields = $allAdditionFields[self::GROUP_NAME];

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($informationModel->getId()) {
            $fieldset->addField(
                'gift_id',
                'hidden',
                ['name' => 'gift_id']
            );
        }

        $fieldset->addField(
            'is_expired',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'disabled' => true,
                'required' => false,
                'values' => [
                    ["value" => 0,"label" => __("Active")],
                    ["value" => 1,"label" => __("Expired")],
                ]
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'disabled' => true,
                'required' => false,
                'value' => 'N/A'
            ]
        );

        $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => __('Type'),
                'title' => __('Type'),
                'disabled' => true,
                'required' => false,
                'value' => 'N/A'
            ]
        );

        $fieldset->addField(
            'location',
            'text',
            [
                'name' => 'location',
                'label' => __('Location'),
                'title' => __('Location'),
                'disabled' => true,
                'required' => false,
                'value' => 'N/A'
            ]
        );

        $fieldset->addField(
            'date',
            'text',
            [
                'name' => 'date',
                'label' => __('Date'),
                'title' => __('Date'),
                'disabled' => true,
                'required' => false,
                'value' => 'N/A'
            ]
        );
//        $fieldset->addField(
//            'privacy',
//            'text',
//            [
//                'name' => 'privacy',
//                'label' => __('Privacy'),
//                'title' => __('Privacy'),
//                'disabled' => true,
//                'required' => false,
//                'value' => 'N/A'
//            ]
//        );

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
            $data['title'] = html_entity_decode($data['title'], ENT_QUOTES);
            $data['description'] = html_entity_decode($data['description'] ?? '', ENT_QUOTES);
            $date = new DateTime($data['date']);
            $data['date'] = $date->format('Y-m-d');
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
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('General Information');
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
