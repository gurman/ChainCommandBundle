<?php

namespace Gurman\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chain_command');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('chains')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('main_command')->end()
                            ->arrayNode('members')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('command')->end()
                                        ->scalarNode('arguments')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
