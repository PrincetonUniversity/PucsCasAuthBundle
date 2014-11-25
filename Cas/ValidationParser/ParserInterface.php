<?php

namespace Pucs\CasAuthBundle\Cas\ValidationParser;

/**
 * Interface ParserInterface
 */
interface ParserInterface
{
    /**
     * Parses the validation response from the CAS server.
     *
     * @param string $response
     *
     * @return ValidationResponse
     */
    function parseResponse($response);
}
