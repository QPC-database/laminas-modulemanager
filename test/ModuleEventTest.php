<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager;

use Laminas\ModuleManager\Listener\ConfigListener;
use Laminas\ModuleManager\ModuleEvent;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class ModuleEventTest extends TestCase
{
    public function setUp()
    {
        $this->event = new ModuleEvent();
    }

    public function testCanRetrieveModuleViaGetter()
    {
        $module = new stdClass;
        $this->event->setModule($module);
        $test = $this->event->getModule();
        $this->assertSame($module, $test);
    }

    public function testPassingNonObjectToSetModuleRaisesException()
    {
        $this->setExpectedException('Laminas\ModuleManager\Exception\InvalidArgumentException');
        $this->event->setModule('foo');
    }

    public function testCanRetrieveModuleNameViaGetter()
    {
        $moduleName = 'MyModule';
        $this->event->setModuleName($moduleName);
        $test = $this->event->getModuleName();
        $this->assertSame($moduleName, $test);
    }

    public function testPassingNonStringToSetModuleNameRaisesException()
    {
        $this->setExpectedException('Laminas\ModuleManager\Exception\InvalidArgumentException');
        $this->event->setModuleName(new StdClass);
    }

    public function testSettingConfigListenerProxiesToParameters()
    {
        $configListener = new ConfigListener;
        $this->event->setConfigListener($configListener);
        $test = $this->event->getParam('configListener');
        $this->assertSame($configListener, $test);
    }

    public function testCanRetrieveConfigListenerViaGetter()
    {
        $configListener = new ConfigListener;
        $this->event->setConfigListener($configListener);
        $test = $this->event->getConfigListener();
        $this->assertSame($configListener, $test);
    }
}
