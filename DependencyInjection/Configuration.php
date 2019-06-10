<?php

namespace ADW\SonataMediaChunkUploader\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('adw_chunk_uploader');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('adw_chunk_uploader');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

        $rootNode
            ->children()
                ->arrayNode('chunks')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('chunk_size')
                            ->info('Set the maximum size per chunk in bytes, default values is 1Mb')
                            ->defaultValue(1048576)
                        ->end()
                        ->scalarNode('chunk_folder')
                            ->info('folder for uploading chunks to.')
                            ->defaultNull()
                        ->end()
                        ->booleanNode('load_distribution')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('maxage')
                            ->defaultValue(604800)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('max_size')
                            ->defaultValue(\PHP_INT_MAX)
                            ->info('Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                        ->end()
                        ->enumNode('type')
                            ->values(['filesystem'])
                            ->defaultValue('filesystem')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}