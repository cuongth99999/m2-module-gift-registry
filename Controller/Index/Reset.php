<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/5/17
 * Time: 4:14 PM
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;

/**
 * Class Reset
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Reset extends AbstractAction
{
    /**
     * @var GiftRegistryFactory
     */
    protected $_eventFactory;

    /**
     * @var GiftRegistry
     */
    protected $_eventResource;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Reset constructor.
     * @param LoggerInterface $logger
     * @param GiftRegistryFactory $eventFactory
     * @param GiftRegistry $eventResource
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        LoggerInterface $logger,
        GiftRegistryFactory $eventFactory,
        GiftRegistry $eventResource,
        Context $context,
        Data $data
    ) {
        $this->_logger = $logger;
        $this->_eventFactory = $eventFactory;
        $this->_eventResource = $eventResource;
        return parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws AlreadyExistsException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $registry = $this->_eventFactory->create();
        $this->_eventResource->load($registry, $params['event_id']);
        $registry->setImage('');
        $this->_eventResource->save($registry);
        $this->messageManager->addSuccessMessage(__('The image has reset successfully!'));
        return $this->resultRedirectFactory->create()->setPath('giftregistry/index/manageregistry/', ['type'=>$params['type'],'event_id'=> $params['event_id']]);
    }
}
