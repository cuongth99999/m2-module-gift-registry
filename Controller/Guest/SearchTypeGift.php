<?php
/**
 * Created by PhpStorm.
 * User: thienmagenest
 * Date: 17/07/2017
 * Time: 13:37
 */

namespace Magenest\GiftRegistry\Controller\Guest;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class SearchTypeGift
 * @package Magenest\GiftRegistry\Controller\Guest
 */
class SearchTypeGift extends AbstractAction
{
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $_eventFactory;

    /**
     * @var Context
     */
    protected $_context;

    /**
     * ListSearch constructor.
     * @param CollectionFactory $eventFactory
     * @param Context $context
     * @param Data $data
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        CollectionFactory $eventFactory,
        Context $context,
        Data $data,
        PageFactory $resultPageFactory
    ) {
        $this->_context = $context;
        $this->_eventFactory = $eventFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
