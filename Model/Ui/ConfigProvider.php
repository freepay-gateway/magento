<?php
namespace FreePay\Gateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'freepay_gateway';

    const XML_PATH_CARD_LOGO = 'payment/freepay_gateway/cardlogos';

    protected $scopeConfig;

    protected $assetRepo;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'redirectUrl' => 'freepaygateway/payment/redirect',
                    'paymentLogo' => $this->getFreePayCardLogo()
                ]
            ]
        ];
    }

    public function getFreePayCardLogo(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $cards = explode(',', $this->scopeConfig->getValue(self::XML_PATH_CARD_LOGO, $storeScope));

        $items = [];

        if(count($cards)) {
            foreach ($cards as $card) {
                if($card) {
                    $items[] = $this->assetRepo->getUrl("freepay_gateway::images/logo/{$card}.png");
                }
            }
        }

        return $items;
    }
}
