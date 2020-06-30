<?php

namespace SMS\FluidComponents\Tests\Unit;

use SMS\FluidComponents\Service\XsdGenerator;
use SMS\FluidComponents\Utility\ComponentLoader;

class XsdGeneratorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var XsdGenerator null
     */
    protected $generator = null;
    protected $fluidNamespaces = null;
    protected $componentLoader = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->componentLoader = new ComponentLoader();
        $this->generator = new XsdGenerator($this->componentLoader);
        $this->fluidNamespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'];
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }


    /**
     * @test
     * @throws \ReflectionException
     */
    public function getFileNameForNamespace() {

        $this->assertEquals(
            'SMS_FluidComponents_Components.xsd',
            $this->invokeMethod($this->generator,'getFileNameForNamespace', [ 'SMS\FluidComponents\Components' ])
        );
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function getDefaultPrefixForNamespace() {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\FluidComponents\ViewHelpers';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['ab'][] = 'SMS\OtherComponents\Components';
        $this->assertEquals(
            'fc',
            $this->invokeMethod($this->generator, 'getDefaultPrefixForNamespace', ['SMS\FluidComponents\ViewHelpers']));
        $this->assertEquals(
            'ab',
            $this->invokeMethod($this->generator, 'getDefaultPrefixForNamespace', ['SMS\OtherComponents\Components']));
        $this->assertEquals(
            'me',
            $this->invokeMethod($this->generator, 'getDefaultPrefixForNamespace', ['Vendor\MyExension\Components']));

    }

    public function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] = $this->fluidNamespaces;
        parent::tearDown();
    }
}
