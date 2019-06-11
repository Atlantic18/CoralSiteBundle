<?php

namespace Coral\SiteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('coral_site');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('coral_site');

        $rootNode
            ->children()
                ->scalarNode('content_path')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/Resources/Content')
                    ->info('Path to the content repository')
                ->end()
                ->scalarNode('config_path')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/Resources/Configuration')
                    ->info('Path to the configuration repository')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
