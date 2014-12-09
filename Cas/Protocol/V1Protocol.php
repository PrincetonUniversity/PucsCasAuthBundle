<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Cas\Protocol;

/**
 * Class V1Protocol
 */
class V1Protocol extends AbstractProtocol
{
    /**
     * {@inheritdoc}
     */
    public function getValidationUri($service, $ticket)
    {
        $fullServiceUrl = $this->getCompleteServiceUri($service);

        return $this->buildUri(
            'validate',
            array(
                'service' => $fullServiceUrl,
                'ticket' => $ticket,
            )
        );
    }
}
