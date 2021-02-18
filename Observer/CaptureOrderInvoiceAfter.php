<?php

namespace FreePay\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CaptureOrderInvoiceAfter implements ObserverInterface
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

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        if ($payment->getMethod() === \FreePay\Gateway\Model\Ui\ConfigProvider::CODE) {
            $captureCase = $invoice->getRequestedCaptureCase();
            if ($payment->canCapture()) {
                if ($captureCase == \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE) {
                    $parts = explode('-', $payment->getLastTransId());
                    $transaction = $parts[0];

                    try {
                        $this->adapter->capture($order, $transaction, $order->getGrandTotal());
                    } catch (LocalizedException $e) {
                        throw new LocalizedException(__($e->getMessage()));
                    }
                }
            }
        }
    }
}