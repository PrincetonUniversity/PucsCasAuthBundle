<?php

namespace Pucs\CasAuthBundle\Cas\Protocol;

/**
 * Class V2Protocol
 */
class V2Protocol extends AbstractProtocol
{
    /**
     * {@inheritdoc}
     */
    public function getValidationUri($service, $ticket)
    {
        $fullServiceUrl = $this->getCompleteServiceUri($service);

        return $this->buildUri(
            'serviceValidate',
            array(
                'service' => $fullServiceUrl,
                'ticket' => $ticket,
            )
        );
    }
}
