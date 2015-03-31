<?php

namespace Coral\SiteBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 * @codeCoverageIgnore
 */
class FilterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('coral.renderer'))
        {
            $definition = $container->getDefinition('coral.renderer');

            $taggedServices = $container->findTaggedServiceIds('coral.renderer.filter');

            foreach ($taggedServices as $id => $tagAttributes)
            {
                foreach ($tagAttributes as $attributes)
                {
                    $definition->addMethodCall(
                        'addFilter',
                        array(new Reference($id), $attributes["type"])
                    );
                }
            }
        }
    }
}