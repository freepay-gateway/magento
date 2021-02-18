<?php

namespace FreePay\Gateway\Controller\Payment;

use Magento\Sales\Model\Order;
use Zend\Json\Json;

class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var FreePay\Gateway\Model\Adapter\FreePayAdapter
     */
    protected $adapter;

    /**
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * @var \FreePay\Gateway\Helper\FreePayCom
     */
    protected $client;

    /**
     * Class constructor
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \FreePay\Gateway\Model\Adapter\FreePayAdapter $adapter,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \FreePay\Gateway\Helper\FreePayCom $client
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->dir = $dir;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->adapter = $adapter;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceSender = $invoiceSender;
        $this->client = $client;

        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->dir->getRoot().'/var/log/freepay.log'));

        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Handle callback from FreePay
     *
     * @return string
     */
    public function execute()
    {
        $this->logger->debug('CALLBACK');
        $body = $this->getRequest()->getContent();
        try {
            parse_str($body, $response);
            $this->logger->debug(json_encode($response));

            $order = $this->order->loadByIncrementId($response['order_id']);

            if($order->getId()) {
                if($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING){
                    return;
                }
            }
            else {
                $this->logger->debug('Failed to load order with id: ' . $response['order_id']);
                return;
            }

            $payment = $order->getPayment();
            if($payment->getLastTransId()){
                return;
            }

            //Add card metadata
            $payment->setIsTransactionClosed(0);
            $transactionId = $response['authorizationIdentifier'];

            $info = json_decode($this->client->get($transactionId));

            $cardType = '';

            switch($info['CardType']) {
                case -1:
                    $cardType = 'Unknown';
                    break;
                case 0:
                    $cardType = 'AmericanExpressDanish';
                    break;
                case 1:
                    $cardType = 'AmericanExpressForeign';
                    break;
                case 2:
                    $cardType = 'DinersDanish';
                    break;
                case 3:
                    $cardType = 'DinersForeign';
                    break;
                case 4:
                    $cardType = 'MastercardForeign';
                    break;
                case 5:
                    $cardType = 'MastercardDanish';
                    break;
                case 6:
                    $cardType = 'VisaDankort';
                    break;
                case 7:
                    $cardType = 'VisaElectronDanish';
                    break;
                case 8:
                    $cardType = 'VisaElectronForeign';
                    break;
                case 9:
                    $cardType = 'VisaDanish';
                    break;
                case 10:
                    $cardType = 'VisaForeign';
                    break;
                case 11:
                    $cardType = 'JCB';
                    break;
                case 12:
                    $cardType = 'ElectronOrVisaForeign';
                    break;
                case 13:
                    $cardType = 'Dankort';
                    break;
                case 14:
                    $cardType = 'MaestroDanish';
                    break;
                case 15:
                    $cardType = 'MaestroForeign';
                    break;
                case 16:
                    $cardType = 'MastercardDebitDanish';
                    break;
            }

            $payment->setCcType($cardType);
            $payment->setCcLast4($info['MaskedPan']);
            $payment->setCcExpMonth(explode('/', $info['CardExpiryDate'][0]));
            $payment->setCcExpYear(explode('/', $info['CardExpiryDate'])[1]);

            $payment->setAdditionalInformation('Transaction ID', $response['authorizationIdentifier']);
            $payment->setAdditionalInformation('Card Type', $cardType);
            $payment->setAdditionalInformation('Card Number', $info['MaskedPan']);
            $payment->setAdditionalInformation('Card Expiration Date', date('Y-m', strtotime(explode('/', $info['CardExpiryDate'])[0].'-'.explode('/', $info['CardExpiryDate'])[1])));
            $payment->setAdditionalInformation('Currency', $info['Currency']);

            //Set order to processing
            $stateProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;

            if ($order->getState() !== $stateProcessing) {
                $order->setState($stateProcessing)
                    ->setStatus($order->getConfig()->getStateDefaultStatus($stateProcessing))
                    ->save();
            }

            $this->adapter->createTransaction($order, $response['authorizationIdentifier'], \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

            //Send order email
            if (!$order->getEmailSent()) {
                $this->sendOrderConfirmation($order);
            }

            $this->getResponse()->setBody("OK");
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Send order confirmation email
     *
     * @param \Magento\Sales\Model\Order $order
     */
    private function sendOrderConfirmation($order)
    {
        try {
            $this->orderSender->send($order);
            $order->addStatusHistoryComment(__('Order confirmation email sent to customer'))
                ->setIsCustomerNotified(true)
                ->save();
        } catch (\Exception $e) {
            $order->addStatusHistoryComment(__('Failed to send order confirmation email: %s', $e->getMessage()))
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
