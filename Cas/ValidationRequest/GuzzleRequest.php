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

use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Client as GuzzleClient;
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
     * @param GuzzleClient $guzzleClient
     * @param mixed        $serverCaValidation
     */
    public function __construct(GuzzleClient $guzzleClient, $serverCaValidation)
    {
        parent::__construct($serverCaValidation);
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * {@inheritdoc}
     */
    public function sendValidationRequest($uri)
    {
        try {
            $this->guzzleClient->setSslVerification($this->serverCaValidation);
            $request = $this->guzzleClient->get($uri);
            $response = $request->send();

            return (string) $response->getBody();
        } catch (RuntimeException $e) {
            throw new ValidationException("Validation request to CAS server failed with message: " . $e->getMessage());
        }
    }
}
