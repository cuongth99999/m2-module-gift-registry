<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 22/06/2017
 * Time: 15:40
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magenest\GiftRegistry\Controller\Adminhtml\GiftRegistry;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Delete extends GiftRegistry
{

    /**
     * @var CollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry
     */
    protected $giftRegistryResource;

    /**
     * @var Registrant
     */
    protected $registrantResource;

    /**
     * @var Item
     */
    protected $itemResource;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param GiftRegistryFactory $_giftregistryFactory
     * @param RegistrantFactory $_registrantFactory
     * @param ItemFactory $_itemFactory
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Filter $filter
     * @param UrlRewriteFactory $urlRewrite
     * @param ForwardFactory $resultForwardFactory
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param ScopeConfigInterface $configInterface
     * @param CollectionFactory $optionCollectionFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry $giftRegistryResource
     * @param Registrant $registrantResource
     * @param Item $itemResource
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        GiftRegistryFactory $_giftregistryFactory,
        RegistrantFactory $_registrantFactory,
        ItemFactory $_itemFactory,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Filter $filter,
        UrlRewriteFactory $urlRewrite,
        ForwardFactory $resultForwardFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        ScopeConfigInterface $configInterface,
        CollectionFactory $optionCollectionFactory,
        GiftRegistryFactory $giftRegistryFactory,
        \Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry $giftRegistryResource,
        Registrant $registrantResource,
        Item $itemResource
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $_giftregistryFactory, $_registrantFactory, $_itemFactory, $resultRawFactory, $layoutFactory, $filter, $urlRewrite, $resultForwardFactory, $productFactory, $categoryFactory, $configInterface);
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->giftRegistryResource = $giftRegistryResource;
        $this->registrantResource = $registrantResource;
        $this->itemResource = $itemResource;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $registryIds = $this->getRequest()->getParam('registrant_id');

        if (empty($registryIds)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $this->deleteRegistry($registryIds);
                $this->messageManager->addSuccessMessage(__('A total of 1 record have been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * delete gift registry
     *
     * @param $id
     * @throws \Exception
     */
    public function deleteRegistry($id)
    {
        $registration = $this->_registrantFactory->create();
        $this->registrantResource->load($registration, $id)->delete($registration);
        if ($registration['giftregistry_id']) {
            $registry = $this->giftRegistryFactory->create();
            $this->giftRegistryResource->load($registry, $registration['giftregistry_id'])->delete($registry);

            if ($registry) {
                $items = $this->_itemFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('gift_id', $registration['giftregistry_id'])
                    ->getData();
                foreach ($items as $item) {
                    $options = $this->optionCollectionFactory->create()->addFieldToFilter('gift_item_id', $item['gift_item_id']);
                    foreach ($options as $option) {
                        $option->delete();
                    }
                    $itemLoader = $this->_itemFactory->create();
                    $this->itemResource->load($itemLoader, $item['gift_item_id'])->delete($itemLoader);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::manager');
    }
}
