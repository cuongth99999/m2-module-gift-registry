<?php
namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Cron
 * @package Magenest\GiftRegistry\Model
 */
class Cron
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Cron constructor.
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @throws AlreadyExistsException
     */
    public function execute()
    {
        $this->dataHelper->updateExpiredGift();
    }

    /**
     * @throws LocalizedException
     */
    public function deleteGRTmp()
    {
        $this->dataHelper->deleteGRTmp();
    }
}
