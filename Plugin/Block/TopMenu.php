<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 08/07/2017
 * Time: 14:35
 */

namespace Magenest\GiftRegistry\Plugin\Block;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Context;

/**
 * Class TopMenu
 * @package Magenest\GiftRegistry\Plugin\Block
 */
class TopMenu
{
    /**
     * @var NodeFactory
     */
    protected $_nodeFactory;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Data
     */
    protected $data;

    /**
     * TopMenu constructor.
     * @param Context $context
     * @param NodeFactory $nodeFactory
     * @param Data $data
     */
    public function __construct(
        Context $context,
        NodeFactory $nodeFactory,
        Data $data
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_nodeFactory = $nodeFactory;
        $this->data = $data;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if($this->data->isEnableExtension()){
            $node = $this->_nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray(),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree()
                ]
            );
            $subject->getMenu()->addChild($node);
        }
    }

    /**
     * @return array
     */
    protected function getNodeAsArray()
    {
        return [
            'name' => __('Gift Registry'),
            'id' => 'gift-registry',
            'url' => $this->_urlBuilder->getUrl('gift-registry.html'),
            'has_active' => false,
            'is_active' => false // (expression to determine if menu item is selected or not)
        ];
    }
}
