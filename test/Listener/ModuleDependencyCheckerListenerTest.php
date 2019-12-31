<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleDependencyCheckerListenerTest extends TestCase
{
    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
    public function testCallsGetModuleDependenciesOnModuleImplementingInterface()
    {
        //$moduleManager = new ModuleManager(array());
        //$moduleManager->getEventManager()->attach('loadModule', new ModuleDependencyCheckerListener(), 2000);

        $module = $this->getMock('Laminas\ModuleManager\Feature\DependencyIndicatorInterface');
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue(array()));

        $event = $this->getMock('Laminas\ModuleManager\ModuleEvent');
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
    public function testCallsGetModuleDependenciesOnModuleNotImplementingInterface()
    {
        $module = $this->getMock('stdClass', array('getModuleDependencies'));
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue(array()));

        $event = $this->getMock('Laminas\ModuleManager\ModuleEvent');
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $listener->__invoke($event);
    }

    /**
     * @covers \Laminas\ModuleManager\Listener\ModuleDependencyCheckerListener::__invoke
     */
    public function testNotFulfilledDependencyThrowsException()
    {
        $module = $this->getMock('stdClass', array('getModuleDependencies'));
        $module->expects($this->once())->method('getModuleDependencies')->will($this->returnValue(array('OtherModule')));

        $event = $this->getMock('Laminas\ModuleManager\ModuleEvent');
        $event->expects($this->any())->method('getModule')->will($this->returnValue($module));

        $listener = new ModuleDependencyCheckerListener();
        $this->setExpectedException('Laminas\ModuleManager\Exception\MissingDependencyModuleException');
        $listener->__invoke($event);
    }
}
