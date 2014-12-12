<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PucsCasAuthExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XMLFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('pucs.cas_auth.server.base_server_uri', $processedConfig['server']['base_server_uri']);
        $container->setParameter('pucs.cas_auth.server.ca_validation', $processedConfig['server']['ca_validation']);

        // Load the services we need for selected protocol version.
        $loader->load('cas_v' . $processedConfig['server']['version'] . '.xml');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return "pucs_cas_auth";
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
    }
}
