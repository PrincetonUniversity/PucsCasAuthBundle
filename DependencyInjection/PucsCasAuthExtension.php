<?php

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

        $container->setParameter('pucs.cas_auth.server.version', $processedConfig['server']['version']);
        $container->setParameter('pucs.cas_auth.server.login_url', $processedConfig['server']['login_url']);
        $container->setParameter('pucs.cas_auth.server.logout_url', $processedConfig['server']['logout_url']);
        $container->setParameter('pucs.cas_auth.server.validate_url', $processedConfig['server']['validate_url']);
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
