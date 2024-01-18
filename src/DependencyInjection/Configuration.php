<?php
declare(strict_types=1);

namespace Choks\ResetCode\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): NodeParentInterface
    {
        $treeBuilder = new TreeBuilder('reset_code');

        /**
         * @phpstan-ignore-next-line
         */
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->booleanNode('enabled')
                        ->info('Setting bundle off. Probably for debugging purposes')
                        ->defaultTrue()
                    ->end()
                    ->arrayNode('tables')
                        ->defaultValue([])
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')
                                    ->info('Table name that will be created.')
                                ->end()
                                ->scalarNode('alias')
                                    ->defaultNull()
                                    ->info('Alias used to get service, ex. reset_code.YOUR_ALIAS. If not set, name used.')
                                ->end()
                                ->scalarNode('connection_name')
                                    ->defaultValue('default')
                                    ->info("DBAL Connection. If not set 'default' is used.")
                                ->end()
                                ->scalarNode('code_size')
                                    ->defaultValue(6)
                                    ->info('Code size. For example 795181 is 6 in size.')
                                ->end()
                                ->scalarNode('ttl')
                                    ->defaultValue(6)
                                    ->info('Time in seconds, for code to be valid. Basically, time to expire.')
                                ->end()
                                ->scalarNode('timeout_to_clear_oldest_ms')
                                    ->defaultValue(250)
                                    ->info('If does not find unused code, will clear oldest one created.')
                                ->end()
                                ->booleanNode('allow_subject_duplicates')
                                    ->defaultFalse()
                                    ->info('Allow more than one record of same subject.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}