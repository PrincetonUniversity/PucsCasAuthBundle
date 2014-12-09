<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle;

use Pucs\CasAuthBundle\Security\Factory\CasFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class PucsCasAuthBundle
 */
class PucsCasAuthBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new CasFactory());
    }
}
