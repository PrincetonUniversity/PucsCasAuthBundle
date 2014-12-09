<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
