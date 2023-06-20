<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Grid
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Grid extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Product grid for AJAX request
     *
     * @return Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('index');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::manager');
    }
}
