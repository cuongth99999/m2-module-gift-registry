<?php
namespace Magenest\GiftRegistry\Controller\Registry;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryTmpFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Preview
 * @package Magenest\GiftRegistry\Controller\Registry
 */
class Preview extends AbstractAction
{
    /** @var PageFactory $_pageFactory */
    protected $_pageFactory;

    /** @var GiftRegistryTmpFactory $_giftregistryTmpFactory */
    protected $_giftregistryTmpFactory;

    /** @var GiftRegistryTmp $_giftregistryTmpResource */
    protected $_giftregistryTmpResource;

    /**
     * Preview constructor.
     * @param PageFactory $pageFactory
     * @param GiftRegistryTmpFactory $giftregistryTmpFactory
     * @param GiftRegistryTmp $giftregistryTmpResource
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        PageFactory $pageFactory,
        GiftRegistryTmpFactory $giftregistryTmpFactory,
        GiftRegistryTmp $giftregistryTmpResource,
        Context $context,
        Data $data
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_giftregistryTmpFactory = $giftregistryTmpFactory;
        $this->_giftregistryTmpResource = $giftregistryTmpResource;
        parent::__construct($context, $data);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $giftregistryTmp = $this->_giftregistryTmpFactory->create();
        $this->_giftregistryTmpResource->load($giftregistryTmp, $params['gift_id']);
        if (!$giftregistryTmp->getData()) {
            $this->messageManager->addErrorMessage(__('The preview you\'re looking for is no longer exists.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('gift_registry/customer/mygiftregistry/');
        }
        $resultPage =  $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__("Preview Gift Registry"));
        return $resultPage;
    }
}
