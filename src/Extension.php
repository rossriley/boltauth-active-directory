<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory;

use Bolt\Extension\AbstractExtension;
use Bolt\Extension\BoltAuth\ActiveDirectory\Provider\ActiveDirectory;
use Bolt\Extension\MenuTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Auth management extension for Bolt
 *
 *
 * @author    Ross Riley <riley.ross@gmail.com>
 */
class Extension extends AbstractExtension implements ServiceProviderInterface
{
    use MenuTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['auth.oauth.handler.activedirectory'] = $app::protect(
            function () use ($app) {
                return new Handler\ActiveDirectory($app['auth.config'], $app);
            }
        );

        $app['auth.oauth.provider.map'] = $app::share(
            $app->extend(
                'auth.oauth.provider.map',
                function ($map) {
                    $map['activedirectory'] = ActiveDirectory::class;

                    return $map;
                }
            )
        );

        $app['auth.oauth.provider.activedirectory'] = $app::share(function ($app){
           return new ActiveDirectory();
        });


    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $this->container = $app;
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
     * Returns the service provider.
     *
     * @return array
     */
    public function getServiceProviders()
    {
        return [
            $this,
        ];
    }
}
