<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 04/05/2016
 * Time: 15:55
 */
namespace Magenest\GiftRegistry\Model\Email;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Review\Model\ResourceModel\Review\ColletionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Mail
 * @package Magenest\GiftRegistry\Model\Email
 */
class Mail
{
    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;
    /**
     * @var ColletionFactory
     */
    protected $_review;
    /**
     * @var
     */
    protected $_product;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var TransportBuilder
     */
    private $_transportBuilder;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Mail constructor.
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param Session $customerSession
     */
    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Session $customerSession
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->_customerSession = $customerSession;
    }

    /**
     * @param $recipient
     * @param $subject
     * @param $message
     * @param $tranId
     * @throws \Exception
     */
    public function sendMail($recipient, $subject, $message, $tranId)
    {
        if ($tranId != null) {
            $template_id = $this->_scopeConfig->getValue(
                'giftregistry/email/thank_you_email_form',
                ScopeInterface::SCOPE_STORE
            );
        } else {
            $template_id = $this->_scopeConfig->getValue(
                'giftregistry/email/email_template_share_email',
                ScopeInterface::SCOPE_STORE
            );
        }

        try {
            if (!$template_id) {
                throw new \Exception(__('No email template selected.'));
            }
            $this->inlineTranslation->suspend();
            $from = $this->_scopeConfig->getValue(
                'giftregistry/email/sender',
                ScopeInterface::SCOPE_STORE
            );
            $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )->setTemplateVars(
                [
                    'subject' => $subject,
                    'message' => $message,
                ]
            )->setFromByScope(
                $from
            )->addTo(
                $recipient,
                'friends'
            )->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            throw new \Exception($e);
        }
    }
}
