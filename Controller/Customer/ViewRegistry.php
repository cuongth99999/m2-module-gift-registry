<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 01/12/2015
 * Time: 13:25
 */
namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ViewRegistry
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class ViewRegistry extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * ViewRegistry constructor.
     * @param Context $context
     * @param Data $data
     * @param Session $session
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        Session $session,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $session;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return ResultInterface|Layout|Page
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addWarningMessage(__('Please login to continue'));
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('customer/account/login');
            return $resultForward;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Registry Items'));
        return $resultPage;
    }
}
