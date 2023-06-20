<?php
namespace Magenest\GiftRegistry\Plugin\Block;

use Magenest\GiftRegistry\Helper\Cart;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class MultishippingAddresses
 * @package Magenest\GiftRegistry\Plugin\Block
 */
class MultishippingAddresses
{
    /**
     * @var Cart
     */
    private $_quoteHelper;

    /**
     * @var Config
     */
    private $_addressConfig;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $_addressRepository;

    /**
     * @var GiftRegistryFactory
     */
    private $_giftRegistryFactory;

    /**
     * @var GiftRegistry
     */
    private $_giftRegistryResources;

    /**
     * @var Mapper
     */
    private $_addressMapper;

    /**
     * MultishippingAddresses constructor.
     * @param Cart $quoteHelper
     * @param Config $addressConfig
     * @param AddressRepositoryInterface $addressRepository
     * @param Mapper $addressMapper
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResources
     * @param LoggerInterface $logger
     */
    public function __construct(
        Cart $quoteHelper,
        Config $addressConfig,
        AddressRepositoryInterface $addressRepository,
        Mapper $addressMapper,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResources,
        LoggerInterface $logger
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->_addressConfig = $addressConfig;
        $this->_addressRepository = $addressRepository;
        $this->_addressMapper = $addressMapper;
        $this->_giftRegistryFactory = $giftRegistryFactory;
        $this->_giftRegistryResources = $giftRegistryResources;
        $this->_logger = $logger;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetAddressOptions($subject, $result)
    {
        $giftRegistryId = $this->_quoteHelper->getRegistryId();
        if ($giftRegistryId) {
            $giftRegistry = $this->_giftRegistryFactory->create();
            $this->_giftRegistryResources->load($giftRegistry, $giftRegistryId);
            $addressId = $giftRegistry->getData('shipping_address');

            try {
                $address = $this->_addressRepository->getById($addressId);
            } catch (LocalizedException $e) {
                $this->_logger->critical($e->getMessage());
                return $result;
            }

            $label = $this->_addressConfig
                ->getFormatByCode(Config::DEFAULT_ADDRESS_FORMAT)
                ->getRenderer()
                ->renderArray($this->_addressMapper->toFlatArray($address));

            $result[] = [
                'value' => $addressId,
                'label' => $label,
            ];
        }

        return $result;
    }
}
