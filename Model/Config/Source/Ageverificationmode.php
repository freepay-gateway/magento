<?php

namespace Freepay\Gateway\Model\Config\Source;

class Ageverificationmode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Module rounding mode
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'ageverificationdisabled', 'label' => "Disabled"],
            ['value' => 'ageverificationenabled', 'label' => "Enabled"],
        ];
    }
}
