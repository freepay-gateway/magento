<?php

namespace FreePay\Gateway\Model\Config\Source;

class PaymentLogo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'dankort',
                'label' => __('Dankort')
            ],
            [
                'value' => 'visa',
                'label' => __('VISA')
            ],
            [
                'value' => 'mastercard',
                'label' => __('MasterCard')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            '' => __('All payment methods'),
            'creditcard' => __('All creditcards'),
            'specified' => __('As specified')
        ];
    }
}
