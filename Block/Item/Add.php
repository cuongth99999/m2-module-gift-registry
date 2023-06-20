<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:38
 */
namespace Magenest\GiftRegistry\Block\Item;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Block\Item
 */
class Add extends Template
{
    /**
     * Template name
     * @var string
     */
    protected $_template = 'Magenest_GiftRegistry::link.phtml';

    /**
     * @var Data
     */
    protected $_registryHelper;

    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_registryHelper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_productRepository = $productRepository;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_registryHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('gift_registry/guest/search');
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return __('My Gift Registry');
    }

    /**
     * Retrieve current product model
     *
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->_productRepository->getById($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }
}
