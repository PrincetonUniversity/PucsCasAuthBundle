<?php

namespace Pucs\CasAuthBundle\Cas\Validator;

use Pucs\CasAuthBundle\Cas\Protocol\ProtocolInterface;
use Pucs\CasAuthBundle\Cas\ValidationRequest\RequestInterface;
use Pucs\CasAuthBundle\Cas\ValidationParser\ParserInterface;
use Pucs\CasAuthBundle\Exception\ValidationException;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * @var RequestInterface
     */
    protected $validationRequest;

    /**
     * @var ParserInterface
     */
    protected $validationParser;

    /**
     * @param ProtocolInterface $protocol
     * @param RequestInterface  $validationRequest
     * @param ParserInterface   $validationParser
     */
    public function __construct(ProtocolInterface $protocol, RequestInterface $validationRequest, ParserInterface $validationParser)
    {
        $this->protocol = $protocol;
        $this->validationRequest = $validationRequest;
        $this->validationParser = $validationParser;
    }

    /**
     * Validate the provided ticket with the CAS server and return a populated ValidationResponse object.
     *
     * @param string $ticket
     * @param string $service
     *
     * @throws ValidationException
     *
     * @return \Pucs\CasAuthBundle\Cas\CasLoginData
     */
    public function validate($ticket, $service)
    {
        if (empty($ticket)) {
            throw new ValidationException("Missing service ticket needed for validation.");
        }

        if (empty($service)) {
            throw new ValidationException("Missing service path needed for validation.");
        }

        $validationUrl = $this->protocol->getValidationUri($service, $ticket);

        $response = $this->validationRequest->sendValidationRequest($validationUrl);

        return $this->validationParser->parseResponse($response);
    }
}
