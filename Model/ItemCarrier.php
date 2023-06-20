<?php

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Wishlist\Model\Wishlist;
use Psr\Log\LoggerInterface as Logger;

class ItemCarrier
{
    /**
     * @var GiftRegistryProviderInterface
     */
    protected $_giftRegistryProvider;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ItemCarrier constructor.
     * @param GiftRegistryProviderInterface $giftRegistryProvider
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Logger $logger
     */
    public function __construct(
        GiftRegistryProviderInterface $giftRegistryProvider,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Logger $logger
    ){
        $this->_giftRegistryProvider = $giftRegistryProvider;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Move all wishlist item to gift registry
     *
     * @param Wishlist $wishlist
     * @param array $qtys
     * @param string $giftregistry
     * @return Forward
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function moveAllToGiftRegistry(Wishlist $wishlist, $qtys, $giftregistry)
    {
        /** @var  GiftRegistry $giftRegistry */
        $giftRegistry = $this->_giftRegistryProvider->getGiftRegistry();
        $messages = [];

        if (!$giftRegistry) {
            $this->messageManager->addErrorMessage(__('Page not found.'));
        }
        if (isset($params['giftregistry'])) {
            $giftRegistryId = intval($params['giftregistry']);
            if (!is_numeric($giftRegistryId)) {
                //forward the customer to customer account page
                /** @var Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->setModule('customer');
                $resultForward->setController('account');
                return $resultForward->forward('create');
            }
        }

        $products = [];
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();
        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */

            $product = $item->getProduct();
            if ($product != null) {
                $product_type = $product->getTypeId();
                if ($product_type == 'simple' || $product_type == 'configurable') {
                    $buyRequest = $item->getBuyRequest();
                    $buyRequest->setData('giftregistry', $giftregistry);
                    if($product_type == 'configurable' && !$buyRequest->getSuperAttribute()){
                        $messages[] = __('You need to choose options for your item for "%1".', $item->getProduct()->getName());
                        continue;
                    }
                    try {
                        $giftRegistry->addNewItem($product, $buyRequest);
                        $products[] = '"' . $product->getName() . '"';
                        $item->delete();
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $messages[] = __('We can\'t add this item to your gift registry right now.');
                    }
                }else{
                    $messages[] = __('The type of the product ', $item->getProduct()->getName() . ' is not supported to add to gift registry!');
                }
            }
        }

        if ($products) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t update the Wish List right now.'));
            }

            $this->messageManager->addSuccessMessage(
                __('%1 product(s) have been added to gift registry: %2.', count($products), join(', ', $products))
            );
        }

        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        }
    }
}
