<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 13/07/2017
 * Time: 17:34
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ListGift
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ListGift extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * RegistryView constructor.
     * @param Context $context
     * @param Data $data
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context, $data);
    }

    /**
     * @param null $coreRoute
     * @return Page
     */
    public function execute($coreRoute = null)
    {
        return $this->_pageFactory->create();
    }
}
