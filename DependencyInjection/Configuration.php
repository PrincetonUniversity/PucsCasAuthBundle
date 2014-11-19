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
                        ->scalarNode('login_url')->isRequired()->end()
                        ->scalarNode('logout_url')->isRequired()->end()
                        ->scalarNode('validate_url')->isRequired()->end()
                        ->scalarNode('ca_pem')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
