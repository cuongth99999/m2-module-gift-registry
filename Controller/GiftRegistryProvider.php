<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/12/2015
 * Time: 16:15
 */
namespace Magenest\GiftRegistry\Controller;

use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry as GiftRegistryResources;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class GiftRegistryProvider
 * @package Magenest\GiftRegistry\Controller
 */
class GiftRegistryProvider implements GiftRegistryProviderInterface
{
    /**
     * @var GiftRegistry
     */
    protected $giftRegistry;

    /**
     * @var GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var GiftRegistryResources
     */
    protected $giftRegistryResources;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * GiftRegistryProvider constructor.
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistryResources $giftRegistryResources
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistryResources $giftRegistryResources,
        Session $customerSession,
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->giftRegistryResources = $giftRegistryResources;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function getGiftRegistry($id = null)
    {
        $customerId = $this->customerSession->getCustomerId();
        $giftRegistry = $this->giftRegistryFactory->create();

        if ($id) {
            $this->giftRegistryResources->load($giftRegistry, $id);
        } else {
            $this->giftRegistryResources->load($giftRegistry, $customerId);
        }

        return $giftRegistry;
    }
}
