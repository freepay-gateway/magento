<?php

namespace FreePay\Gateway\Controller\Payment;

class Returns extends \Magento\Framework\App\Action\Action
{
    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Redirect to to checkout success
     *
     * @return void
     */
    public function execute()
    {
        $area = $this->getRequest()->getParam('area');
        if($area == 'admin'){
            $this->messageManager->addSuccess(__('Thank you for your purchase. You will soon receive a confirmation by email.'));
        }

        if ($this->_getCheckout()->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }
}