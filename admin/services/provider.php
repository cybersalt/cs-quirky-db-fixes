<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Cybersalt\Component\CsQuirkyDbFixes\Administrator\Extension\CsQuirkyDbFixesComponent;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\Cybersalt\\Component\\CsQuirkyDbFixes'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Cybersalt\\Component\\CsQuirkyDbFixes'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new CsQuirkyDbFixesComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
