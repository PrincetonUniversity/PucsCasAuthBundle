<?php

namespace Pucs\CasAuthBundle\Cas\ValidationRequest;

/**
 * Interface RequestInterface
 */
interface RequestInterface
{
    /**
     * Send a validation request to the CAS server, and return the response body.
     *
     * The URI should contain both "ticket" and "service" query string params
     * which the CAS server uses for validation.
     *
     * @param string $uri
     *
     * @return string
     */
    public function sendValidationRequest($uri);
}
