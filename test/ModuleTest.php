<?php
declare(strict_types=1);

namespace AcMailerTest;

use AcMailer\Module;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ModuleTest extends TestCase
{
    /** @var Module */
    private $module;

    public function setUp()
    {
        $this->module = new Module();
    }

    /**
     * @test
     */
    public function getConfigReturnsContentsFromModuleConfigFile()
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $returnedConfig = $this->module->getConfig();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }

    /**
     * @test
     */
    public function invokeReturnsContentsFromModuleConfigFile()
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $expectedConfig['dependencies'] = $expectedConfig['service_manager'];
        unset($expectedConfig['service_manager']);
        $returnedConfig = $this->module->__invoke();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }
}
