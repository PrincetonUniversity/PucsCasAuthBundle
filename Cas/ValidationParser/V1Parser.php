<?php

namespace Pucs\CasAuthBundle\Cas\ValidationParser;

/**
 * Class V1Parser
 */
class V1Parser implements ParserInterface
{
    /**
     * Parse CAS server validation response content.
     *
     * @param string $content
     *
     * @return ValidationResponse
     */
    public function parseResponse($content)
    {
        $data = explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $content)));
        $success = strtolower($data[0] === 'yes');

        $response = new ValidationResponse();

        if ($success) {
            // Extract the username from the message field and return it.
            $username = (count($data) > 1 && $data[1]) ? $data[1] : null;
            if ($username) {
                $response->setUsername($username);
                $response->setSuccess();
            } else {
                $response->setFailure('Malformed data');
            }
        } else {
            $response->setFailure('Unknown failure');
        }

        return $response;
    }
}
