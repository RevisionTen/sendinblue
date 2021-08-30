<?php

namespace RevisionTen\Sendinblue\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sendinblue');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')->end()
                ->arrayNode('campaigns')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('list_id')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
