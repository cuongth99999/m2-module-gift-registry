<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/4/17
 * Time: 8:49 AM
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab;

use Magenest\GiftRegistry\Model\Status;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Images
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit\Tab
 */
class Images extends FormGeneric implements TabInterface
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
     * @return FormGeneric
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('type');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('images_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Background Image')]);

        $fieldset->addType('image', 'Magenest\GiftRegistry\Block\Adminhtml\Helper\Image');

        $sizeHint = '<p style="color:red;position: absolute;width: auto;top: 54px;" class="label" id="error-type-file"></p>';
        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Banner Default'),
                'title' => __('Banner Default'),
                'after_element_html' => $sizeHint,
                'enctype'=> 'multipart/form-data',
                'note' => 'You should upload image type: jpg, jpeg, png (Optimal size is 1600 x 1100 px)'
            ]
        );
        $fieldset->addField(
            'thumnail',
            'image',
            [
                'name' => 'thumnail',
                'label' => __('Thumbnail Default'),
                'title' => __('Thumbnail Default'),
                'enctype'=> 'multipart/form-data',
                'after_element_html' => $sizeHint,
                'note' => 'You should upload image type: jpg, jpeg, png (Optimal size is 270 x 370 px)'
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
        return __('Background Image');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Background Image');
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
