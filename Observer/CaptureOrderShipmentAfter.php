<?php

namespace FreePay\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CaptureOrderShipmentAfter implements ObserverInterface
{
    /**
     * @var FreePay\Gateway\Model\Adapter\FreePayAdapter
     */
    protected $adapter;

    public function __construct(
        \FreePay\Gateway\Model\Adapter\FreePayAdapter $adapter
    )
    {
        $this->adapter = $adapter;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();

        $payment = $order->getPayment();
        if ($payment->getMethod() === \FreePay\Gateway\Model\Ui\ConfigProvider::CODE) {
            $parts = explode('-', $payment->getLastTransId());
            $order = $payment->getOrder();
            $transaction = $parts[0];

            try {
                $this->adapter->capture($order,$transaction, $order->getGrandTotal());
            } catch (LocalizedException $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
    }
}