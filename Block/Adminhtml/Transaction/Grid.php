<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 22:51
 */
namespace Magenest\GiftRegistry\Block\Adminhtml\Transaction;

use Magenest\GiftRegistry\Model\Status;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Block\Adminhtml\Transaction
 */
class Grid extends Extended
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var TranFactory
     */
    protected $_tranFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param TranFactory $tranFactory
     * @param Status $status
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        TranFactory $tranFactory,
        Status $status,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->_tranFactory = $tranFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tranGrid');
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
        $collection = $this->_tranFactory->create()->getCollection();

        $salesTable =  $collection->getResource()->getTable('sales_order');

        $collection->getSelect()->join(['q'=>$salesTable], 'q.entity_id = main_table.order_id');

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
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order Id'),
                'type' => 'text',
                'index' => 'order_id'
            ]
        );
        $this->addColumn(
            'customer_firstname',
            [
                'header' => __('Customer First Name'),
                'type' => 'text',
                'index' => 'customer_firstname'
            ]
        );
        $this->addColumn(
            'customer_lastname',
            [
                'header' => __('Customer Last Name'),
                'type' => 'text',
                'index' => 'customer_lastname'
            ]
        );
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'type' => 'text',
                'index' => 'customer_email'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'Order',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getOrderId',
                'actions' => [
                    [
                        'caption' => __('View Order'),
                        'url' => [
                                'base' => 'sales/order/view'
                        ],
                        'field' => 'order_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        $this->addColumn(
            'Gift Registry',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getGiftregistryId',
                'actions' => [
                    [
                        'caption' => __('View Gift Registry'),
                        'url' => [
                            'base' => '*/registry/edit'
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
        $this->setMassactionIdField('order_id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftregistry/*/grid', ['_current' => true]);
    }
}
