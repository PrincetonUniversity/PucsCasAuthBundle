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

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\RequestException;
use Pucs\CasAuthBundle\Exception\ValidationException;

/**
 * Class GuzzleRequest
 */
class GuzzleRequest extends AbstractRequest
{
    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @param string       $caPem
     * @param GuzzleClient $guzzleClient
     */
    public function __construct($caPem, GuzzleClient $guzzleClient)
    {
        parent::__construct($caPem);
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * {@inheritdoc}
     */
    public function sendValidationRequest($uri)
    {
        // If user provided a CA PEM file to verify the CAS server, attach it to request.
        // Otherwise we don't perform any validation.
        if (!empty($this->caPem)) {
            $this->guzzleClient->setSslVerification($this->caPem);
        } else {
            $this->guzzleClient->setSslVerification(false);
        }

        try {
            $request = $this->guzzleClient->get($uri);
            $response = $request->send();

            return (string) $response->getBody();
        } catch (RequestException $e) {
            throw new ValidationException("Validation request to CAS server failed with message: " . $e->getMessage());
        }
    }
}
