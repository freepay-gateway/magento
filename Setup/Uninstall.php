<?php

namespace FreePay\Gateway\Setup {

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{    
    protected $eavSetupFactory;

    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();    
        $eavSetup = $this->eavSetupFactory->create();    
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ageVerification');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'ageVerification');
        $setup->endSetup();    
    }
}

}

?>