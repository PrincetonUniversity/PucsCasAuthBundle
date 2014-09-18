<?php

namespace Pucs\CasAuthBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class CasFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    public function createAuthProvider(ContainerBuilder $container, $id, $config, $userProvider)
    {
        $providerId = 'security.authentication.provider.cas.' . $id;

        $container
            ->setDefinition($providerId, new DefinitionDecorator('pucs.cas_auth.security.authentication.provider'))
            ->replaceArgument(1, new Reference($userProvider))
        ;

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerId()
    {
        return 'pucs.cas_auth.security.authentication.listener';
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'security.authentication.entry_point.'  . $id;

        // Create a service for our custom entry point based on our existing "template", and pass
        // in the factory config as an additional argument.
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('pucs.cas_auth.security.authentication.entry_point'))
            ->addArgument($config)
        ;

        return $entryPointId;
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'cas';
    }
}
