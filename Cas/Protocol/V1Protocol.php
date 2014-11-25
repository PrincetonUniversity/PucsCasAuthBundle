<?php

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
