<?php

namespace Magenest\GiftRegistry\Block\LayoutProcessor\Checkout;


use Magenest\GiftRegistry\Helper\Data;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class GiftRegistryProcessors implements LayoutProcessorInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * GiftRegistryProcessors constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ){
        $this->data = $data;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($jsLayout)
    {
        if(!$this->data->isEnableExtension()){
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
                ['children']['shippingAdditional']['children']['giftregistry-guest-message']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
                ['children']['before-form']['children']['shipping_registry']);
        }

        return $jsLayout;
    }
}
