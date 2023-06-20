<?php
namespace Magenest\GiftRegistry\Block\Adminhtml\Helper;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;

/**
 * Class GiftRegsitrySection
 * @package Magenest\GiftRegistry\Block\Adminhtml\Helper
 */
class GiftRegsitrySection extends AbstractElement
{
    /** @var  Registry $_coreRegistry */
    protected $_coreRegistry;

    /**
     * GiftRegsitrySection constructor.
     * @param Registry $coreRegistry
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $typeModel = $this->_coreRegistry->registry('type');
        $html = "";
        $data = $typeModel->getData();
        if ($data['fields']) {
//            $fields = ZendJson::decode($data['fields'],ZendJson::TYPE_ARRAY);
            $html .= "<div class='admin__field field'>
                        <label class='label admin__field-label' for='about_event'><span>" . __('About Event') . "</span></label>
                        <div class='admin__field-control control'>
                            <input id='about_event' name='field[]' value='about_event' title='About Event' type='checkbox' class='required-entry _required' aria-required='true'>
                        </div>
                      </div>";
            $html .= "<div class='admin__field field'>
                        <label class='label admin__field-label' for='general_info'><span>" . __('General Information') . "</span></label>
                        <div class='admin__field-control control'>
                            <input id='general_info' name='field[]' value='general_info' title='General Information' type='checkbox' class='required-entry _required' aria-required='true'>
                        </div>
                      </div>";
            $html .= "<div class='admin__field field'>
                        <label class='label admin__field-label' for='registrant_info'><span>" . __('Registrant Information') . "</span></label>
                        <div class='admin__field-control control'>
                            <input id='registrant_info' name='field[]' value='registrant_info' title='Registrant Information' type='checkbox' class='required-entry _required' aria-required='true'>
                        </div>
                      </div>";
            $html .= "<div class='admin__field field'>
                        <label class='label admin__field-label' for='privacy'><span>" . __('Privacy') . "</span></label>
                        <div class='admin__field-control control'>
                            <input id='privacy' name='field[]' value='privacy' title='General Information' type='checkbox' class='required-entry _required' aria-required='true'>
                        </div>
                      </div>";
            $html .= "<div class='admin__field field'>
                        <label class='label admin__field-label' for='shipping_address_info'><span>" . __('Shipping Address Information') . "</span></label>
                        <div class='admin__field-control control'>
                            <input id='shipping_address_info' name='field[]' value='shipping_address_info' title='Shipping Address Information' type='checkbox' class='required-entry _required' aria-required='true'>
                        </div>
                      </div>";
        }
        return $html;
    }
}
