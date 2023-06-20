<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */
namespace Magenest\GiftRegistry\Model;

/**
 * Class Status
 * @package Magenest\GiftRegistry\Model
 */
class Status
{
    /**#@+
     * Blog Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 2;

    const IS_ACTIVE = 1;

    const IS_INACTIVE = 2;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param  string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public static function getOptionArrayActive()
    {
        return [self::IS_ACTIVE=> __('Active'), self::IS_INACTIVE => __('Inactive')];
    }

    /**
     * @return array
     */
    public function getAllOptionactive()
    {
        $result = [];

        foreach (self::getOptionArrayActive() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * @param $optionId
     * @return mixed|null
     */
    public function getOptionTextActive($optionId)
    {
        $options = self::getOptionArrayActive();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
