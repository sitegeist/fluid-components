<?php

namespace SMS\FluidComponents\Tests\Unit\Utility;

use SMS\FluidComponents\Utility\ComponentSettings;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class ComponentSettingsTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected $componentSettings;
    protected $tsfe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->componentSettings = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] = [];

        $this->tsfe = $GLOBALS['TSFE'] ?? null;
        $GLOBALS['TSFE'] = new \StdClass;
        $GLOBALS['TSFE']->tmpl = new \StdClass;
        $GLOBALS['TSFE']->tmpl->setup = [];

        $this->settings = new ComponentSettings(new TypoScriptService());
    }

    /**
     * @test
     */
    public function settingsProvidedByPhpArray()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] = [
            'nested' => ['mySetting' => 'myValue'],
            'anotherSetting' => 'anotherValue'
        ];
        $this->settings->reset();

        $this->assertEquals('anotherValue', $this->settings->get('anotherSetting'));
        $this->assertEquals('myValue', $this->settings->get('nested.mySetting'));
        $this->assertEquals(['mySetting' => 'myValue'], $this->settings->get('nested'));
        $this->assertEquals(null, $this->settings->get('nonexistentSetting'));

        $this->assertEquals('anotherValue', $this->settings['anotherSetting']);
        $this->assertEquals('myValue', $this->settings['nested']['mySetting']);
    }

    /**
     * @test
     */
    public function settingsProvidedByPhpTypoScript()
    {
        $GLOBALS['TSFE']->tmpl->setup['config.']['tx_fluidcomponents.']['settings.'] = [
            'nested.' => ['mySetting' => 'myValue'],
            'anotherSetting' => 'anotherValue'
        ];
        $this->settings->reset();

        $this->assertEquals('anotherValue', $this->settings->get('anotherSetting'));
        $this->assertEquals('myValue', $this->settings->get('nested.mySetting'));
        $this->assertEquals(['mySetting' => 'myValue'], $this->settings->get('nested'));
        $this->assertEquals(null, $this->settings->get('nonexistentSetting'));

        $this->assertEquals('anotherValue', $this->settings['anotherSetting']);
        $this->assertEquals('myValue', $this->settings['nested']['mySetting']);

        unset($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function settingsMergedFromPhpArrayAndTypoScript()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] = [
            'nested' => ['mySetting' => 'myValue'],
            'anotherSetting' => 'anotherValue'
        ];
        $GLOBALS['TSFE']->tmpl->setup['config.']['tx_fluidcomponents.']['settings.'] = [
            'nested.' => ['myNewSetting' => 'myNewValue'],
            'anotherSetting' => 'newValue',
            'additionalSetting' => 'additionalValue'
        ];
        $this->settings->reset();

        $this->assertEquals('newValue', $this->settings->get('anotherSetting'));
        $this->assertEquals('additionalValue', $this->settings->get('additionalSetting'));
        $this->assertEquals(null, $this->settings->get('nested.mySetting'));
        $this->assertEquals(['myNewSetting' => 'myNewValue'], $this->settings->get('nested'));
        $this->assertEquals(null, $this->settings->get('nonexistentSetting'));

        $this->assertEquals('newValue', $this->settings['anotherSetting']);
        $this->assertEquals('myNewValue', $this->settings['nested']['myNewSetting']);

        unset($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function settingsProvidedByApi()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] = [
            'nested' => ['mySetting' => 'myValue'],
            'anotherSetting' => 'anotherValue'
        ];
        $this->settings->reset();

        $this->settings->set('nested.mySetting', 'apiValue');
        $this->settings->set('nested.myApiSetting', 'anotherApiValue');

        $this->assertEquals('anotherValue', $this->settings->get('anotherSetting'));
        $this->assertEquals('apiValue', $this->settings->get('nested.mySetting'));
        $this->assertEquals(
            ['mySetting' => 'apiValue', 'myApiSetting' => 'anotherApiValue'],
            $this->settings->get('nested')
        );
        $this->assertEquals(null, $this->settings->get('nonexistentSetting'));

        $this->assertEquals('anotherValue', $this->settings['anotherSetting']);
        $this->assertEquals('apiValue', $this->settings['nested']['mySetting']);
    }

    /**
     * @test
     */
    public function unsetSettingByApi()
    {
        $this->settings->set('mySetting', 'myValue');
        $this->assertEquals('myValue', $this->settings->get('mySetting'));
        $this->settings->unset('mySetting');
        $this->assertEquals(null, $this->settings->get('mySetting'));
    }

    public function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] = $this->componentSettings;
        $GLOBALS['TSFE'] = $this->tsfe;
        parent::tearDown();
    }
}
