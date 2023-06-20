<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 19/06/2017
 * Time: 09:24
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Class GiftRegistry
 * @package Magenest\GiftRegistry\Controller\Adminhtml
 */
abstract class GiftRegistry extends Action
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scoreConfig;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;
    /**
     * @var Filter
     */
    protected $_filter;
    /**
     * @var Context
     */
    protected $_context;

    /**
     * GiftRegistry constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param GiftRegistryFactory $_giftregistryFactory
     * @param RegistrantFactory $_registrantFactory
     * @param ItemFactory $_itemFactory
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Filter $filter
     * @param UrlRewriteFactory $urlRewrite
     * @param ForwardFactory $resultForwardFactory
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param ScopeConfigInterface $configInterface
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        GiftRegistryFactory $_giftregistryFactory,
        RegistrantFactory $_registrantFactory,
        ItemFactory $_itemFactory,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Filter $filter,
        UrlRewriteFactory $urlRewrite,
        ForwardFactory $resultForwardFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        ScopeConfigInterface $configInterface
    ) {
        $this->_registrantFactory   = $_registrantFactory;
        $this->_itemFactory         = $_itemFactory;
        $this->_categoryFactory     = $categoryFactory;
        $this->_productFactory      = $productFactory;
        $this->_context             = $context;
        $this->_coreRegistry        = $coreRegistry;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->resultRawFactory     = $resultRawFactory;
        $this->layoutFactory        = $layoutFactory;
        $this->_giftregistryFactory = $_giftregistryFactory;
        $this->_filter              = $filter;
        $this->_urlRewrite          = $urlRewrite;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_scoreConfig         = $configInterface;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        return $this->_resultPageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
