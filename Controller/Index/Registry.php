<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 12/2/17
 * Time: 01:09
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Registry
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Registry extends AbstractAction
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * RegistryLogin constructor.
     * @param Session $session
     * @param ResultJsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry $registry
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Session $session,
        ResultJsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        Context $context,
        Data $data
    ) {
        $this->_session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $registry;
        return parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $request = $param['request'];
        if ($request == 'registry_login') {
            $this->_session->setRegistryLogin(true);
            $this->_session->setRegistryType($param['type']);
        }
        return $this->resultJsonFactory->create()->setData(['registry' => 'success']);
        // TODO: Implement execute() method.
    }
}
