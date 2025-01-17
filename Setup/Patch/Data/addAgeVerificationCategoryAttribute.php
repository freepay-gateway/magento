<?php
 
namespace FreePay\Gateway\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class addAgeVerificationCategoryAttribute implements DataPatchInterface
{
    // @var ModuleDataSetupInterface
    
    private $moduleDataSetup;
    
    // @var EavSetupFactory
    
    private $eavSetupFactory;

    // @param ModuleDataSetupInterface $moduleDataSetup
    // @param EavSetupFactory $eavSetupFactory
        
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) 
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    //Add eav attributes
    public function apply()
    {
        // @var EavSetup $eavSetup
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY, 
            'ageVerification', 
            [
            'type'         => 'int',
            'label'        => 'Age Verification',
            'input'        => 'select',
            'required'     => false,
            'visible'      => true,
            'default'      => '',
            'source'       => 'FreePay\Gateway\Model\Config\Source\AgeVerificationOptions',
            'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'        => 'General',
            'required'     => false,
			'is_user_defined' => true,
        ]);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}

?>
