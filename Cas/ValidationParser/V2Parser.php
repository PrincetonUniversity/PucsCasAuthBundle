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

use Pucs\CasAuthBundle\Cas\CasLoginData;

/**
 * Class V2Parser
 */
class V2Parser implements ParserInterface
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
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->encoding = "utf-8";
        $data = new CasLoginData();

        if (@$dom->loadXML($content) !== false) {
            $failureElements = $dom->getElementsByTagName('authenticationFailure');
            if ($failureElements->length == 0) {
                $successElements = $dom->getElementsByTagName("authenticationSuccess");
                if ($successElements->length == 1) {
                    // There should only be one success element, grab it and extract username.
                    $successElement = $successElements->item(0);
                    $userElement = $successElement->getElementsByTagName("user");
                    if ($userElement->length == 1) {
                        $data->setUsername($userElement->item(0)->nodeValue);
                        $data->setSuccess();
                    } else {
                        $data->setFailure('Malformed CAS validation data.');
                    }
                } else {
                    // All reponses should have either an authenticationFailure
                    // or authenticationSuccess node.
                    $data->setFailure('Malformed CAS validation data.');
                }
            } else {
                $failureElement = $failureElements->item(0);
                $errorCode = $failureElement->getAttribute('code');
                $errorMsg = $failureElement->nodeValue;

                $data->setFailure('CAS Validation error: ' . trim($errorCode) . ': ' . trim($errorMsg));
            }
        } else {
            $data->setFailure('Malformed CAS validation data.');
        }

        return $data;
    }
}
