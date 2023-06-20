<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:45
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Registry;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Block\Adminhtml\Registry
 */
class Grid extends Extended
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var GiftRegistryFactory|RegistrantFactory
     */
    protected $_registryFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param RegistrantFactory $eventFactory
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        RegistrantFactory $eventFactory,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->_registryFactory= $eventFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('registryGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_registryFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */

    protected function _prepareColumns()
    {
        $this->addColumn(
            'registrant_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'registrant_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'type' => 'text',
                'index' => 'email'
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'type' => 'text',
                'index' => 'firstname'
            ]
        );
        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'type' => 'text',
                'index' => 'lastname'
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header' => __('Created at'),
                'type' => 'text',
                'index' => 'created_time'
            ]
        );
        $this->addColumn(
            'updated_time',
            [
                'header' => __('Updated Time'),
                'type' => 'text',
                'index' => 'updated_time'
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'registrant_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('event_id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('registrant_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('giftregistry/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftregistry/*/grid', ['_current' => true]);
    }

    /**
     * @param Product|DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'giftregistry/*/edit',
            ['registrant_id' => $row->getId()]
        );
    }
}
