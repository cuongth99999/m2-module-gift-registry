<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 14:23
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class MyGiftRegistry
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class MyGiftRegistry extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Data $data
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Customer order history
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Gift Registry'));
        return $resultPage;
    }
}
