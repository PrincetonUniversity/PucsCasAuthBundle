<?php

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
