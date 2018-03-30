<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory;

use Bolt\Extension\AbstractExtension;
use Bolt\Extension\BoltAuth\Auth\AccessControl\SessionSubscriber;
use Bolt\Extension\BoltAuth\Auth\EventListener\ProfileListener;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\TranslationTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auth management extension for Bolt
 *
 *
 * @author    Ross Riley <riley.ross@gmail.com>
 */
class Extension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    use MenuTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['auth.oauth.handler.activedirectory'] = $app->protect(
            function () use ($app) {
                return new Handler\ActiveDirectory($app['auth.config'], $app);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $this->container = $app;
        $this->subscribe($app['dispatcher']);
    }

    /**
     * Define events to listen to here.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $app = $this->getContainer();
        $dispatcher->addSubscriber($this);
        $dispatcher->addSubscriber(new SessionSubscriber($app));
        $dispatcher->addSubscriber(new ProfileListener($app));
    }

    protected function registerMenuEntries()
    {
        $config = $this->getConfig();
        $roles = isset($config['roles']['admin']) ? $config['roles']['admin'] : ['root'];

        return [
            (new MenuEntry('authconfig', 'authconfig'))
                ->setLabel(Trans::__('Auth Configuration'))
                ->setIcon('fa:users')
                ->setPermission(implode('||', $roles)),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [];
    }
}
