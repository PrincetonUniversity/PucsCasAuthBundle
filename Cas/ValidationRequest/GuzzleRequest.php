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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Pucs\CasAuthBundle\Exception\ValidationException;

/**
 * Class GuzzleRequest
 */
class GuzzleRequest extends AbstractRequest
{
    private $client;

    /**
     * @param Client $client
     * @param mixed  $serverCaValidation
     */
    public function __construct(Client $client, $serverCaValidation)
    {
        parent::__construct($serverCaValidation);
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function sendValidationRequest($uri)
    {
        $request = $this->client->createRequest('GET', $uri, array(
            'verify' => $this->serverCaValidation,
        ));

        try {
            $response = $this->client->send($request);

            if ($response->getStatusCode() == "200") {
                return $response->getBody();
            } else {
                throw new ValidationException('Received invalid status code of "' . $response->getStatusCode() . '" when making CAS validation request.');
            }
        } catch (ClientException $e) {
            throw new ValidationException('Received a ClientException when making CAS validation request: ' . $e->getMessage(), 0, $e);
        } catch (ServerException $e) {
            throw new ValidationException('Received a ServerException when making CAS validation request: ' . $e->getMessage(), 0, $e);
        }
    }
}
