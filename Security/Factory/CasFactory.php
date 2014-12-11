<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CasFactory
 */
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
            ->replaceArgument(3, $config['create_users']);

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
        $entryPointId = 'security.authentication.entry_point.' . $id;

        // Create a service for our custom entry point based on our existing "template", and pass
        // in the factory config as an additional argument.
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('pucs.cas_auth.security.authentication.entry_point'))
            ->addArgument($config);

        return $entryPointId;
    }

    /**
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $builder = $node->children();

        $builder
            ->booleanNode('create_users')
                ->defaultFalse()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'cas';
    }
}
