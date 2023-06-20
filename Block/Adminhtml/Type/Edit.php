<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 11:29
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Type;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type
 */
class Edit extends Container
{
    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize  edit block
     * @return void
     */
    protected function _construct()
    {
        // $this->_objectId = 'id';
        $this->_blockGroup = 'Magenest_GiftRegistry';
        $this->_controller = 'adminhtml_type';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Event Type'));
        $event = $this->_coreRegistry->registry('type');
        if (!$event->getData('id')) {
            $this->buttonList->remove('delete');
        }
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ]
        );

        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Retrieve text for header element depending on loaded post
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('type')->getId()) {
            return __("Edit Event Type '%1'", $this->escapeHtml($this->_coreRegistry->registry('type')->getType()));
        } else {
            return __('New Event Type');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('giftregistry/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
}
