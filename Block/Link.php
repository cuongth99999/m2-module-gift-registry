<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 30/11/2015
 * Time: 11:38
 */
namespace Magenest\GiftRegistry\Block;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Link
 * @package Magenest\GiftRegistry\Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link
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
     * Link constructor.
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->_registryHelper = $helper;
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
}
