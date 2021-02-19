<?php

namespace FreePay\Gateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class FreePayCom extends AbstractHelper
{
    const API_KEY_XML_PATH      = 'payment/freepay_gateway/apikey';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $api_key;

    protected $api_url;

    protected $dir;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;

        $this->api_key = $this->scopeConfig->getValue(self::API_KEY_XML_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->api_url = 'https://mw.freepay.dk/api/authorization/';
        $this->dir = $dir;

        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->dir->getRoot().'/var/log/freepay.log'));
        $this->logger->debug('Com init');
    }

    public function link($form)
    {
        $linkUrl = 'https://gw.freepay.dk/api/payment/';
        return $this->doCurl($linkUrl, json_encode($form), 'POST');
    }

    public function post($path, $form)
    {
        return $this->doCurl($this->api_url . $path, $form, 'POST');
    }

    public function delete($path)
    {
        return $this->doCurl($this->api_url . $path, null, 'DELETE');
    }

    public function get($path)
    {
        return $this->doCurl($this->api_url . $path, null, 'GET');
    }

    public function doCurl($url, $form = null, $method = null)
    {
        $ch = $this->getCurlHandle($url, $form, $method);
        $data = curl_exec($ch);
        if (!$data) {
            $this->logger->critical(curl_error($ch));
        }
        curl_close($ch);

        return $data;
    }

    public function getCurlHandle($url, $form = null, $method = null)
    {
        $ch = curl_init();
        $header = array(
			'Authorization: ' . $this->api_key,
			'Accept: application/json',
			'Content-Type: application/json',
        );
        
        if ($method == null) {
            if ($form) {
                $method = 'POST';
            } else {
                $method = 'GET';
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($form) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
        }
        return $ch;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public static function convertCountryAlphas3To2($code = 'DNK') {
        $countries = json_decode('{"AFG":"AF","ALA":"AX","ALB":"AL","DZA":"DZ","ASM":"AS","AND":"AD","AGO":"AO","AIA":"AI","ATA":"AQ","ATG":"AG","ARG":"AR","ARM":"AM","ABW":"AW","AUS":"AU","AUT":"AT","AZE":"AZ","BHS":"BS","BHR":"BH","BGD":"BD","BRB":"BB","BLR":"BY","BEL":"BE","BLZ":"BZ","BEN":"BJ","BMU":"BM","BTN":"BT","BOL":"BO","BIH":"BA","BWA":"BW","BVT":"BV","BRA":"BR","VGB":"VG","IOT":"IO","BRN":"BN","BGR":"BG","BFA":"BF","BDI":"BI","KHM":"KH","CMR":"CM","CAN":"CA","CPV":"CV","CYM":"KY","CAF":"CF","TCD":"TD","CHL":"CL","CHN":"CN","HKG":"HK","MAC":"MO","CXR":"CX","CCK":"CC","COL":"CO","COM":"KM","COG":"CG","COD":"CD","COK":"CK","CRI":"CR","CIV":"CI","HRV":"HR","CUB":"CU","CYP":"CY","CZE":"CZ","DNK":"DK","DKK":"DK","DJI":"DJ","DMA":"DM","DOM":"DO","ECU":"EC","Sal":"El","GNQ":"GQ","ERI":"ER","EST":"EE","ETH":"ET","FLK":"FK","FRO":"FO","FJI":"FJ","FIN":"FI","FRA":"FR","GUF":"GF","PYF":"PF","ATF":"TF","GAB":"GA","GMB":"GM","GEO":"GE","DEU":"DE","GHA":"GH","GIB":"GI","GRC":"GR","GRL":"GL","GRD":"GD","GLP":"GP","GUM":"GU","GTM":"GT","GGY":"GG","GIN":"GN","GNB":"GW","GUY":"GY","HTI":"HT","HMD":"HM","VAT":"VA","HND":"HN","HUN":"HU","ISL":"IS","IND":"IN","IDN":"ID","IRN":"IR","IRQ":"IQ","IRL":"IE","IMN":"IM","ISR":"IL","ITA":"IT","JAM":"JM","JPN":"JP","JEY":"JE","JOR":"JO","KAZ":"KZ","KEN":"KE","KIR":"KI","PRK":"KP","KOR":"KR","KWT":"KW","KGZ":"KG","LAO":"LA","LVA":"LV","LBN":"LB","LSO":"LS","LBR":"LR","LBY":"LY","LIE":"LI","LTU":"LT","LUX":"LU","MKD":"MK","MDG":"MG","MWI":"MW","MYS":"MY","MDV":"MV","MLI":"ML","MLT":"MT","MHL":"MH","MTQ":"MQ","MRT":"MR","MUS":"MU","MYT":"YT","MEX":"MX","FSM":"FM","MDA":"MD","MCO":"MC","MNG":"MN","MNE":"ME","MSR":"MS","MAR":"MA","MOZ":"MZ","MMR":"MM","NAM":"NA","NRU":"NR","NPL":"NP","NLD":"NL","ANT":"AN","NCL":"NC","NZL":"NZ","NIC":"NI","NER":"NE","NGA":"NG","NIU":"NU","NFK":"NF","MNP":"MP","NOR":"NO","OMN":"OM","PAK":"PK","PLW":"PW","PSE":"PS","PAN":"PA","PNG":"PG","PRY":"PY","PER":"PE","PHL":"PH","PCN":"PN","POL":"PL","PRT":"PT","PRI":"PR","QAT":"QA","REU":"RE","ROU":"RO","RUS":"RU","RWA":"RW","BLM":"BL","SHN":"SH","KNA":"KN","LCA":"LC","MAF":"MF","SPM":"PM","VCT":"VC","WSM":"WS","SMR":"SM","STP":"ST","SAU":"SA","SEN":"SN","SRB":"RS","SYC":"SC","SLE":"SL","SGP":"SG","SVK":"SK","SVN":"SI","SLB":"SB","SOM":"SO","ZAF":"ZA","SGS":"GS","SSD":"SS","ESP":"ES","LKA":"LK","SDN":"SD","SUR":"SR","SJM":"SJ","SWZ":"SZ","SWE":"SE","CHE":"CH","SYR":"SY","TWN":"TW","TJK":"TJ","TZA":"TZ","THA":"TH","TLS":"TL","TGO":"TG","TKL":"TK","TON":"TO","TTO":"TT","TUN":"TN","TUR":"TR","TKM":"TM","TCA":"TC","TUV":"TV","UGA":"UG","UKR":"UA","ARE":"AE","GBR":"GB","USA":"US","UMI":"UM","URY":"UY","UZB":"UZ","VUT":"VU","VEN":"VE","VNM":"VN","VIR":"VI","WLF":"WF","ESH":"EH","YEM":"YE","ZMB":"ZM","ZWE":"ZW","GBP":"GB","RUB":"RU","NOK":"NO"}',true);

        if(!isset($countries[$code])){
            return "";
        } else {
            return $countries[$code];
        }
    }

    /**
     * @param string $code
     * @return mixed
     */
    public static function convertCountryAlphas3ToNumber($code = 'DNK') {
        $countries = json_decode('{"AFG":"004","ALA":"248","ALB":"008","DZA":"012","ASM":"016","AND":"020","AGO":"024","AIA":"660","ATA":"010","ATG":"028","ARG":"032","ARM":"051","ABW":"533","AUS":"036","AUT":"040","AZE":"031","BHS":"044","BHR":"048","BGD":"050","BRB":"052","BLR":"112","BEL":"056","BLZ":"084","BEN":"204","BMU":"060","BTN":"064","BOL":"068","BIH":"070","BWA":"072","BVT":"074","BRA":"076","IOT":"086","BRN":"096","BGR":"100","BFA":"854","BDI":"108","KHM":"116","CMR":"120","CAN":"124","CPV":"132","CYM":"136","CAF":"140","TCD":"148","CHL":"152","CHN":"156","CXR":"162","CCK":"166","COL":"170","COM":"174","COG":"178","COD":"180","COK":"184","CRI":"188","CIV":"384","HRV":"191","CUB":"192","CYP":"196","CZE":"203","DNK":"208","DJI":"262","DMA":"212","DOM":"214","ECU":"218","EGY":"818","SLV":"222","GNQ":"226","ERI":"232","EST":"233","ETH":"231","FLK":"238","FRO":"234","FJI":"243","FIN":"246","FRA":"250","GUF":"254","PYF":"258","ATF":"260","GAB":"266","GMB":"270","GEO":"268","DEU":"276","GHA":"288","GIB":"292","GRC":"300","GRL":"304","GRD":"308","GLP":"312","GUM":"316","GTM":"320","GGY":"831","GIN":"324","GNB":"624","GUY":"328","HTI":"332","HMD":"334","VAT":"336","HND":"340","HKG":"344","HUN":"348","ISL":"352","IND":"356","IDN":"360","IRN":"364","IRQ":"368","IRL":"372","IMN":"833","ISR":"376","ITA":"380","JAM":"388","JPN":"392","JEY":"832","JOR":"400","KAZ":"398","KEN":"404","KIR":"296","PRK":"408","KOR":"410","KWT":"414","KGZ":"417","LAO":"418","LVA":"428","LBN":"422","LSO":"426","LBR":"430","LBY":"434","LIE":"438","LTU":"440","LUX":"442","MAC":"446","MKD":"807","MDG":"450","MWI":"454","MYS":"458","MDV":"462","MLI":"466","MLT":"470","MHL":"584","MTQ":"474","MRT":"478","MUS":"480","MYT":"175","MEX":"484","FSM":"583","MDA":"498","MCO":"492","MNG":"496","MNE":"499","MSR":"500","MAR":"504","MOZ":"508","MMR":"104","NAM":"516","NRU":"520","NPL":"524","NLD":"528","ANT":"530","NCL":"540","NZL":"554","NIC":"558","NER":"562","NGA":"566","NIU":"570","NFK":"574","MNP":"580","NOR":"578","OMN":"512","PAK":"586","PLW":"585","PSE":"275","PAN":"591","PNG":"598","PRY":"600","PER":"604","PHL":"608","PCN":"612","POL":"616","PRT":"620","PRI":"630","QAT":"634","REU":"638","ROU":"642","RUS":"643","RWA":"646","SHN":"654","KNA":"659","LCA":"662","SPM":"666","VCT":"670","WSM":"882","SMR":"674","STP":"678","SAU":"682","SEN":"686","SRB":"688","SYC":"690","SLE":"694","SGP":"702","SVK":"703","SVN":"705","SLB":"090","SOM":"706","ZAF":"729","SSD":"710","SGS":"239","ESP":"724","LKA":"144","SDN":"736","SUR":"740","SJM":"744","SWZ":"748","SWE":"752","CHE":"756","SYR":"760","TWN":"158","TJK":"762","TZA":"834","THA":"764","TLS":"626","TGO":"768","TKL":"772","TON":"776","TTO":"780","TUN":"788","TUR":"792","TKM":"795","TCA":"796","TUV":"798","UGA":"800","UKR":"804","ARE":"784","GBR":"826","USA":"840","UMI":"581","URY":"858","UZB":"860","VUT":"548","VEN":"862","VNM":"704","VGB":"092","VIR":"850","WLF":"876","ESH":"732","YEM":"887","ZMB":"894","ZWE":"716"}',true);

        if(!isset($countries[$code])){
            return "";
        } else {
            return $countries[$code];
        }
    }

    /**
     * @param string $code
     * @return mixed
     */
    public static function convertCountryAlphas2To3($code = 'DK') {
        $countries = json_decode('{"AF":"AFG","AX":"ALA","AL":"ALB","DZ":"DZA","AS":"ASM","AD":"AND","AO":"AGO","AI":"AIA","AQ":"ATA","AG":"ATG","AR":"ARG","AM":"ARM","AW":"ABW","AU":"AUS","AT":"AUT","AZ":"AZE","BS":"BHS","BH":"BHR","BD":"BGD","BB":"BRB","BY":"BLR","BE":"BEL","BZ":"BLZ","BJ":"BEN","BM":"BMU","BT":"BTN","BO":"BOL","BA":"BIH","BW":"BWA","BV":"BVT","BR":"BRA","IO":"IOT","BN":"BRN","BG":"BGR","BF":"BFA","BI":"BDI","KH":"KHM","CM":"CMR","CA":"CAN","CV":"CPV","KY":"CYM","CF":"CAF","TD":"TCD","CL":"CHL","CN":"CHN","CX":"CXR","CC":"CCK","CO":"COL","KM":"COM","CG":"COG","CD":"COD","CK":"COK","CR":"CRI","CI":"CIV","HR":"HRV","CU":"CUB","CY":"CYP","CZ":"CZE","DK":"DNK","DJ":"DJI","DM":"DMA","DO":"DOM","EC":"ECU","EG":"EGY","SV":"SLV","GQ":"GNQ","ER":"ERI","EE":"EST","ET":"ETH","FK":"FLK","FO":"FRO","FJ":"FJI","FI":"FIN","FR":"FRA","GF":"GUF","PF":"PYF","TF":"ATF","GA":"GAB","GM":"GMB","GE":"GEO","DE":"DEU","GH":"GHA","GI":"GIB","GR":"GRC","GL":"GRL","GD":"GRD","GP":"GLP","GU":"GUM","GT":"GTM","GG":"GGY","GN":"GIN","GW":"GNB","GY":"GUY","HT":"HTI","HM":"HMD","VA":"VAT","HN":"HND","HK":"HKG","HU":"HUN","IS":"ISL","IN":"IND","ID":"IDN","IR":"IRN","IQ":"IRQ","IE":"IRL","IM":"IMN","IL":"ISR","IT":"ITA","JM":"JAM","JP":"JPN","JE":"JEY","JO":"JOR","KZ":"KAZ","KE":"KEN","KI":"KIR","KP":"PRK","KR":"KOR","KW":"KWT","KG":"KGZ","LA":"LAO","LV":"LVA","LB":"LBN","LS":"LSO","LR":"LBR","LY":"LBY","LI":"LIE","LT":"LTU","LU":"LUX","MO":"MAC","MK":"MKD","MG":"MDG","MW":"MWI","MY":"MYS","MV":"MDV","ML":"MLI","MT":"MLT","MH":"MHL","MQ":"MTQ","MR":"MRT","MU":"MUS","YT":"MYT","MX":"MEX","FM":"FSM","MD":"MDA","MC":"MCO","MN":"MNG","ME":"MNE","MS":"MSR","MA":"MAR","MZ":"MOZ","MM":"MMR","NA":"NAM","NR":"NRU","NP":"NPL","NL":"NLD","AN":"ANT","NC":"NCL","NZ":"NZL","NI":"NIC","NE":"NER","NG":"NGA","NU":"NIU","NF":"NFK","MP":"MNP","NO":"NOR","OM":"OMN","PK":"PAK","PW":"PLW","PS":"PSE","PA":"PAN","PG":"PNG","PY":"PRY","PE":"PER","PH":"PHL","PN":"PCN","PL":"POL","PT":"PRT","PR":"PRI","QA":"QAT","RE":"REU","RO":"ROU","RU":"RUS","RW":"RWA","SH":"SHN","KN":"KNA","LC":"LCA","PM":"SPM","VC":"VCT","WS":"WSM","SM":"SMR","ST":"STP","SA":"SAU","SN":"SEN","RS":"SRB","SC":"SYC","SL":"SLE","SG":"SGP","SK":"SVK","SI":"SVN","SB":"SLB","SO":"SOM","ZA":"ZAF","SS":"SSD","GS":"SGS","ES":"ESP","LK":"LKA","SD":"SDN","SR":"SUR","SJ":"SJM","SZ":"SWZ","SE":"SWE","CH":"CHE","SY":"SYR","TW":"TWN","TJ":"TJK","TZ":"TZA","TH":"THA","TL":"TLS","TG":"TGO","TK":"TKL","TO":"TON","TT":"TTO","TN":"TUN","TR":"TUR","TM":"TKM","TC":"TCA","TV":"TUV","UG":"UGA","UA":"UKR","AE":"ARE","GB":"GBR","US":"USA","UM":"UMI","UY":"URY","UZ":"UZB","VU":"VUT","VE":"VEN","VN":"VNM","VG":"VGB","VI":"VIR","WF":"WLF","EH":"ESH","YE":"YEM","ZM":"ZMB","ZW":"ZWE"}',true);

        if(!isset($countries[$code])){
            return "";
        } else {
            return $countries[$code];
        }
    }

    /**
     * @param string $code
     * @return mixed
     */
    public static function convertCountryAlphas2ToNumber($code = 'DK') {
        $countries = json_decode('{"AF":"004","AX":"248","AL":"008","DZ":"012","AS":"016","AD":"020","AO":"024","AI":"660","AQ":"010","AG":"028","AR":"032","AM":"051","AW":"533","AU":"036","AT":"040","AZ":"031","BS":"044","BH":"048","BD":"050","BB":"052","BY":"112","BE":"056","BZ":"084","BJ":"204","BM":"060","BT":"064","BO":"068","BA":"070","BW":"072","BV":"074","BR":"076","IO":"086","BN":"096","BG":"100","BF":"854","BI":"108","KH":"116","CM":"120","CA":"124","CV":"132","KY":"136","CF":"140","TD":"148","CL":"152","CN":"156","CX":"162","CC":"166","CO":"170","KM":"174","CG":"178","CD":"180","CK":"184","CR":"188","CI":"384","HR":"191","CU":"192","CY":"196","CZ":"203","DK":"208","DJ":"262","DM":"212","DO":"214","EC":"218","EG":"818","SV":"222","GQ":"226","ER":"232","EE":"233","ET":"231","FK":"238","FO":"234","FJ":"243","FI":"246","FR":"250","GF":"254","PF":"258","TF":"260","GA":"266","GM":"270","GE":"268","DE":"276","GH":"288","GI":"292","GR":"300","GL":"304","GD":"308","GP":"312","GU":"316","GT":"320","GG":"831","GN":"324","GW":"624","GY":"328","HT":"332","HM":"334","VA":"336","HN":"340","HK":"344","HU":"348","IS":"352","IN":"356","ID":"360","IR":"364","IQ":"368","IE":"372","IM":"833","IL":"376","IT":"380","JM":"388","JP":"392","JE":"832","JO":"400","KZ":"398","KE":"404","KI":"296","KP":"408","KR":"410","KW":"414","KG":"417","LA":"418","LV":"428","LB":"422","LS":"426","LR":"430","LY":"434","LI":"438","LT":"440","LU":"442","MO":"446","MK":"807","MG":"450","MW":"454","MY":"458","MV":"462","ML":"466","MT":"470","MH":"584","MQ":"474","MR":"478","MU":"480","YT":"175","MX":"484","FM":"583","MD":"498","MC":"492","MN":"496","ME":"499","MS":"500","MA":"504","MZ":"508","MM":"104","NA":"516","NR":"520","NP":"524","NL":"528","AN":"530","NC":"540","NZ":"554","NI":"558","NE":"562","NG":"566","NU":"570","NF":"574","MP":"580","NO":"578","OM":"512","PK":"586","PW":"585","PS":"275","PA":"591","PG":"598","PY":"600","PE":"604","PH":"608","PN":"612","PL":"616","PT":"620","PR":"630","QA":"634","RE":"638","RO":"642","RU":"643","RW":"646","SH":"654","KN":"659","LC":"662","PM":"666","VC":"670","WS":"882","SM":"674","ST":"678","SA":"682","SN":"686","RS":"688","SC":"690","SL":"694","SG":"702","SK":"703","SI":"705","SB":"090","SO":"706","ZA":"729","SS":"710","GS":"239","ES":"724","LK":"144","SD":"736","SR":"740","SJ":"744","SZ":"748","SE":"752","CH":"756","SY":"760","TW":"158","TJ":"762","TZ":"834","TH":"764","TL":"626","TG":"768","TK":"772","TO":"776","TT":"780","TN":"788","TR":"792","TM":"795","TC":"796","TV":"798","UG":"800","UA":"804","AE":"784","GB":"826","US":"840","UM":"581","UY":"858","UZ":"860","VU":"548","VE":"862","VN":"704","VG":"092","VI":"850","WF":"876","EH":"732","YE":"887","ZM":"894","ZW":"716"}',true);

        if(!isset($countries[$code])){
            return "";
        } else {
            return $countries[$code];
        }
    }
}