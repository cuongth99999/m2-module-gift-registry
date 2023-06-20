<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 7/15/17
 * Time: 3:42 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ListSearch
 * @package Magenest\GiftRegistry\Controller\Index
 */
class ListSearch extends AbstractAction
{
    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * @var CollectionFactory
     */
    protected $_registrantFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * ListSearch constructor.
     * @param LoggerInterface $logger
     * @param RegistrantFactory $registrantFactory
     * @param GiftRegistryFactory $registryFactory
     * @param ResultJsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry $registry
     * @param Context $context
     * @param Data $data
     * @param PageFactory $pageFactory
     */
    public function __construct(
        LoggerInterface $logger,
        RegistrantFactory $registrantFactory,
        GiftRegistryFactory $registryFactory,
        ResultJsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        Context $context,
        Data $data,
        PageFactory $pageFactory
    ) {
        $this->_logger = $logger;
        $this->_registrantFactory = $registrantFactory;
        $this->_registryFactory = $registryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $registry;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context, $data);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage =  $this->_pageFactory->create();
        $type = $this->getRequest()->getParam('type-selected');
        $this->_coreRegistry->register('type', $type);
        return $resultPage;
    }
}
