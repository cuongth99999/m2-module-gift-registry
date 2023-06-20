<?php

/**
 * Created by Magenest.
 * User: trongpq
 * Date: 12/2/17
 * Time: 01:24
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Plugin\Customer\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class LoginPost
 * @package Magenest\GiftRegistry\Plugin\Customer\Account
 */
class LoginPost
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * LoginPost constructor.
     * @param Session $session
     * @param Registry $registry
     * @param UrlInterface $url
     */
    public function __construct(
        Session $session,
        Registry $registry,
        UrlInterface $url
    ) {
        $this->_session = $session;
        $this->_coreRegistry = $registry;
        $this->url = $url;
    }

    /**
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param $resultRedirect
     * @return mixed
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $resultRedirect
    ) {
        $registryLogin = $this->_session->getRegistryLogin('registry_login');
        if ($registryLogin != null && $registryLogin) {
            $type = $this->_session->getRegistryType('type');
            $this->_session->setRegistryLogin(false);
            $this->_session->setRegistryType(null);
            $resultRedirect->setUrl($this->url->getUrl('gift_registry/index/showgift', ['type' => $type]));
            return $resultRedirect;
        }
        return $resultRedirect;
    }
}
