<?php

namespace Pucs\CasAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
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
                        ->integerNode('version')->isRequired()->end()
                        ->scalarNode('base_server_uri')->isRequired()->end()
                        ->scalarNode('ca_pem')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
