<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/03/2016
 * Time: 09:49
 */

namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Customer\Model\Session;
use Magenest\GiftRegistry\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Address extends AbstractAction
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * Address constructor.
     * @param Escaper $escaper
     * @param Logger $logger
     * @param Context $context
     * @param Data $data
     * @param Session $session
     */
    public function __construct(
        Escaper $escaper,
        Logger $logger,
        Context $context,
        Data $data,
        Session $session
    ) {
        $this->_escaper = $escaper;
        $this->_logger = $logger;
        $this->_customerSession = $session;
        parent::__construct($context, $data);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $customer = $this->_customerSession->getCustomer();
        if (!$customer->getId()) {
            return;
        }
        $addresses = $customer->getAddresses();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseData=[];

        if (count($addresses) > 0) {
            foreach ($addresses as $address) {
                $adressId = $address->getData('entity_id');
                $adressLabel = $address->getData('firstname') . ' ' . $address->getData('lastname') . ' ' . $address->getData('street');
                $responseData[]=[
                'id' =>$adressId,
                'label'=>$this->_escaper->escapeHtml($adressLabel)
                ];
            }
        }

        $resultJson->setData($responseData);
        return  $resultJson;
    }
}
