<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 14:53
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'registrant_id';
        $this->_blockGroup = 'Magenest_GiftRegistry';
        $this->_controller = 'adminhtml_registry';

        parent::_construct();
        $this->removeButton('save');
        $this->removeButton('reset');
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('registry')->getId()) {
            return __("Event Registry '%1'", $this->escapeHtml($this->_coreRegistry->registry('registry')->getType()));
        } else {
            return __('Event Registry');
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

    /**
     * Prepare layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        $this->_formScripts[] .= '
            require(["prototype"], function(){
                toggleParentVis("add_review_form");
                toggleVis("save_button");
                toggleVis("reset_button");
            });
        ';

        $this->_formInitScriptss[] = '
            require(["jquery","prototype"], function(jQuery){
            window.review = function() {
                return {
                    productInfoUrl : null,
                    formHidden : true,
                    gridRowClick : function(data, click) {
                        if(Event.findElement(click,\'TR\').title){
                            review.productInfoUrl = Event.findElement(click,\'TR\').title;
                            review.loadProductData();
                            review.showForm();
                            review.formHidden = false;
                        }
                    },
                    loadProductData : function() {
                        jQuery.ajax({
                            type: "POST",
                            url: review.productInfoUrl,
                            data: {
                                form_key: FORM_KEY
                            },
                            showLoader: true,
                            success: review.reqSuccess,
                            error: review.reqFailure
                        });
                    },
                    showForm : function() {
                        toggleParentVis("add_review_form");
                        toggleVis("productGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                    },
                    updateRating: function() {
                        elements = [$("select_stores"), $("rating_detail").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        var params = Form.serializeElements(elements);
                        if (!params.isAjax) {
                            params.isAjax = "true";
                        }
                        if (!params.form_key) {
                            params.form_key = FORM_KEY;
                        }
                        new Ajax.Updater("rating_detail", "' .
            $this->getUrl(
                'review/product/ratingItems'
            ) .
            '", {parameters:params, evalScripts: true,  onComplete:function(){ $(\'save_button\').disabled = false; } });
                    },

                    reqSuccess :function(response) {
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            $("product_id").value = response.id;

                            $("product_name").innerHTML = \'<a href="' .
            $this->getUrl(
                'catalog/product/edit'
            ) .
            'id/\' + response.id + \'" target="_blank">\' + response.name + \'</a>\';
                        } else if ( response.message ) {
                            alert(response.message);
                        }
                    }
                }
            }();
            Event.observe(window, \'load\', function(){
                 if ($("select_stores")) {
                     Event.observe($("select_stores"), \'change\', review.updateRating);
                 }
            });
            });
           //]]>
        ';
        return parent::_prepareLayout();
    }
}
