<?php

namespace Wezz\Yehhpay\Model\Config\Source;

/**
 * Class Mode
 * @package Wezz\Yehhpay\Model\Config\Source
 */
class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '1' => __("Live"),
            '0' => __("Test")
        ];
    }
}
