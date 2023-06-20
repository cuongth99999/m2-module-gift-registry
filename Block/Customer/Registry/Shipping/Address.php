<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 25/12/2015
 * Time: 14:36
 */
namespace Magenest\GiftRegistry\Block\Customer\Registry\Shipping;

use Magento\Customer\Block\Address\Edit;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Address
 * @package Magenest\GiftRegistry\Block\Customer\Registry\Shipping
 */
class Address extends Edit
{
    /**
     * @var string
     */
    protected $_template = "customer/shipping_adress.phtml";

    /**
     * @return array
     */
    public function getRegionData()
    {
        $region = $this->_regionCollectionFactory->create()->getData();

        usort($region, function ($a, $b) {
            if ($a['region_id'] == $b['region_id']) {
                return 0;
            }
            return ($a['region_id'] < $b['region_id']) ? -1 : 1;
        });

        return $region;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getAllRegionSelect()
    {
        $region = $this->_regionCollectionFactory->create()->toOptionArray();
        $value = (int)$this->getRegionId();
        return $this->getLayout()->createBlock(
            Select::class
        )->setName(
            'region_id'
        )->setTitle(
            __('State/Province')
        )->setId(
            'region_id'
        )->setClass(
            'required-entry validate-state'
        )->setValue(
            $value
        )->setOptions(
            $region
        )->getHtml();
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonEncoder->encode($data);
    }
}
