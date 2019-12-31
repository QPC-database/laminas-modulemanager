<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ModuleManager\Listener;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\ModuleManager\Listener\LocatorRegistrationListener;
use Laminas\ModuleManager\Listener\ModuleResolverListener;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ModuleManager\TestAsset\MockApplication;

require_once dirname(__DIR__) . '/TestAsset/ListenerTestModule/src/Foo/Bar.php';

/**
 * @covers Laminas\ModuleManager\Listener\AbstractListener
 * @covers Laminas\ModuleManager\Listener\LocatorRegistrationListener
 */
class LocatorRegistrationListenerTest extends AbstractListenerTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var SharedEventManager
     */
    protected $sharedEvents;

    public function setUp()
    {
        $this->sharedEvents = new SharedEventManager();

        $this->moduleManager = new ModuleManager(['ListenerTestModule']);
        $this->moduleManager->getEventManager()->setSharedManager($this->sharedEvents);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener, 1000);

        $this->application = new MockApplication;
        $events            = new EventManager(['Laminas\Mvc\Application', 'LaminasTest\Module\TestAsset\MockApplication', 'application']);
        $events->setSharedManager($this->sharedEvents);
        $this->application->setEventManager($events);

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setService('ModuleManager', $this->moduleManager);
        $this->application->setServiceManager($this->serviceManager);
    }

    public function testModuleClassIsRegisteredWithDiAndInjectedWithSharedInstances()
    {
        $module = null;
        $locator         = $this->serviceManager;
        $locator->setFactory('Foo\Bar', function (ServiceLocatorInterface $s) {
            $module   = $s->get('ListenerTestModule\Module');
            $manager  = $s->get('Laminas\ModuleManager\ModuleManager');
            $instance = new \Foo\Bar($module, $manager);
            return $instance;
        });

        $locatorRegistrationListener = new LocatorRegistrationListener;
        $this->moduleManager->getEventManager()->attachAggregate($locatorRegistrationListener);
        $this->moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULE, function (ModuleEvent $e) use (&$module) {
            $module = $e->getModule();
        }, -1000);
        $this->moduleManager->loadModules();

        $this->application->bootstrap();
        $sharedInstance1 = $locator->get('ListenerTestModule\Module');
        $sharedInstance2 = $locator->get('Laminas\ModuleManager\ModuleManager');

        $this->assertInstanceOf('ListenerTestModule\Module', $sharedInstance1);
        $foo     = false;
        $message = '';
        try {
            $foo = $locator->get('Foo\Bar');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            while ($e = $e->getPrevious()) {
                $message .= "\n" . $e->getMessage();
            }
        }
        if (!$foo) {
            $this->fail($message);
        }
        $this->assertSame($module, $foo->module);

        $this->assertInstanceOf('Laminas\ModuleManager\ModuleManager', $sharedInstance2);
        $this->assertSame($this->moduleManager, $locator->get('Foo\Bar')->moduleManager);
    }

    public function testNoDuplicateServicesAreDefinedForModuleManager()
    {
        $locatorRegistrationListener = new LocatorRegistrationListener;
        $this->moduleManager->getEventManager()->attachAggregate($locatorRegistrationListener);

        $this->moduleManager->loadModules();
        $this->application->bootstrap();
        $registeredServices = $this->application->getServiceManager()->getRegisteredServices();

        $aliases = $registeredServices['aliases'];
        $instances = $registeredServices['instances'];

        $this->assertContains('laminasmodulemanagermodulemanager', $aliases);
        $this->assertNotContains('modulemanager', $aliases);

        $this->assertContains('modulemanager', $instances);
        $this->assertNotContains('laminasmodulemanagermodulemanager', $instances);
    }
}
