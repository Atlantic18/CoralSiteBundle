<?php

namespace Coral\SiteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Coral\SiteBundle\DependencyInjection\CoralSiteExtension;

class CoralSiteBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new CoralSiteExtension());
    }
}
