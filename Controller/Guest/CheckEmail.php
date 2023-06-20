<?php
/**
 * Created by PhpStorm.
 * User: trongphung
 * Date: 21/06/2017
 * Time: 11:15
 */
namespace Magenest\GiftRegistry\Controller\Guest;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\Email\Mail;
use Magenest\GiftRegistry\Model\GuestsFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Guests;
use Magenest\GiftRegistry\Model\ResourceModel\Tran;
use Magenest\GiftRegistry\Model\TranFactory;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

/***
 * Class CheckEmail
 * @package Magenest\GiftRegistry\Controller\Guest
 */
class CheckEmail extends AbstractAccount
{
    /**
     * @var string
     */
    private static $checkEmailThanks = 'sent';

    /**
     * @var string
     */
    private static $mess = 'Come to our event';

    /**
     * @var string
     */
    private static $welcome = 'Welcome';

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Mail
     */
    protected $_shareMail;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var TranFactory
     */
    protected $_tranFactory;

    /**
     * @var GuestsFactory
     */
    protected $_guestsFactory;

    /**
     * @var Guests
     */
    protected $_guestsResource;

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Tran
     */
    protected $_tranResource;

    /**
     * @var Data
     */
    protected $data;
    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * CheckEmail constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param Mail $mail
     * @param OrderFactory $orderFactory
     * @param TranFactory $tranFactory
     * @param Tran $tranResource
     * @param GuestsFactory $guestsFactory
     * @param Guests $guestsResource
     * @param StoreManager $storeManager
     * @param CustomerFactory $customerFactory
     * @param Data $data
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        Mail $mail,
        OrderFactory $orderFactory,
        TranFactory $tranFactory,
        Tran $tranResource,
        GuestsFactory $guestsFactory,
        Guests $guestsResource,
        StoreManager $storeManager,
        CustomerFactory $customerFactory,
        Data $data,
        Escaper $escaper
    ) {
        $this->_shareMail = $mail;
        $this->_logger=$loggerInterface;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_orderFactory = $orderFactory;
        $this->_tranFactory = $tranFactory;
        $this->_tranResource = $tranResource;
        $this->_guestsFactory = $guestsFactory;
        $this->_guestsResource = $guestsResource;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->data = $data;
        $this->_escaper = $escaper;
    }

    /**
     * @return ResponseInterface|Json|Redirect|ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId != null || !empty($this->getRequest()->getParam('guest_id'))) {
            $this->getDataJson();
            $resultPage = $this->resultRedirectFactory->create();
            $resultPage->setUrl($this->_redirect->getRefererUrl());
            return $resultPage;
        } else {
            $data = $this->getDataJson();
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($data);
        }
    }

    /**
     * send mail registry to all friends
     *
     * @return array
     */
    public function getDataJson()
    {
        $data = $this->getRequest()->getPostValue();
        $emails = explode(',', $data['recipient']);
        $title = $data['email_subject'];
        $linkRegistry = $this->_escaper->escapeHtml($data['message']);
        $linkRegistry = str_replace("\n", "<br>", $linkRegistry);
        if (isset($data['tranId'])) {
            $tranId = $data['tranId'];
        } else {
            $tranId = null;
        }

        $description = 'Done';
        $error = false;
        try {
            $this->_shareMail->sendMail($emails, $title, $linkRegistry, $tranId);
            $this->messageManager->addSuccessMessage(__('Send all emails complete!'));
            if (isset($tranId) && $tranId != null) {
                $registrant = $this->_tranFactory->create();
                $this->_tranResource->load($registrant, $tranId);
                $detail = $registrant->getData();
                $detail['check_email'] = self::$checkEmailThanks;
                $registrant->setData($detail);
                $this->_tranResource->save($registrant);
            } else {
                foreach ($emails as $email) {
                    $giftregistryId = $this->getRequest()->getParam('giftregistryId');
                    $customer = $this->getCustomer($email);
                    $customerId = !empty($customer) ? $customer['entity_id'] : null;
                    $arr = [
                        'customer_id' => $customerId,
                        'customer_email' => $email,
                        'giftregistry_id' => $giftregistryId
                    ];
                    $guestModel = $this->_guestsFactory->create();
                    $guestModel->addData($arr);
                    $this->_guestsResource->save($guestModel);
                }
            }
        } catch (\Exception $e) {
            $error = true;
            $description = $e->getMessage();
            $this->messageManager->addErrorMessage(__("An error has occurred."));
        }

        $params = [];
        $params['description'] = $description;
        $params['error'] = $error;
        return $params;
    }

    /**
     * @param $email
     * @return array|mixed|null
     * @throws NoSuchEntityException
     */
    public function getCustomer($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($websiteId);
        try {
            $customer->loadByEmail($email);
            $data = $customer->getData();
        } catch (\Exception $e) {
            $customerExists = false;
        }
        return $data;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->data->isEnableExtension()) {
            $this->_redirect('noroute');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
}
