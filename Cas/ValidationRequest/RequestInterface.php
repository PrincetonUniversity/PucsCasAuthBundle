<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
