<?php

namespace Pucs\CasAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNote = $treeBuilder->root('pucs_cas_auth');
        $rootNote
            ->children()
                ->arrayNode('server')
                    ->children()
                        ->integerNode('version')->end()
                        ->scalarNode('login_url')->end()
                        ->scalarNode('logout_url')->end()
                        ->scalarNode('validate_url')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
