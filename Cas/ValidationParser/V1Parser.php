<?php

namespace Pucs\CasAuthBundle\Cas\ValidationParser;

use Pucs\CasAuthBundle\Cas\CasLoginData;

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
     * @return CasLoginData
     */
    public function parseResponse($content)
    {
        $splitContent = explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $content)));
        $success = strtolower($splitContent[0] === 'yes');

        $data = new CasLoginData();

        if ($success) {
            // Extract the username from the message field and return it.
            $username = (count($splitContent) > 1 && $splitContent[1]) ? $splitContent[1] : null;
            if ($username) {
                $data->setUsername($username);
                $data->setSuccess();
            } else {
                $data->setFailure('Malformed CAS validation data.');
            }
        } else {
            $data->setFailure('Unknown CAS validation failure.');
        }

        return $data;
    }
}
