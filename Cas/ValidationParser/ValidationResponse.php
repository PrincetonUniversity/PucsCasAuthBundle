<?php

namespace Pucs\CasAuthBundle\Cas\ValidationParser;

/**
 * Class ValidationResponse
 */
class ValidationResponse
{
    protected $username, $success, $failureMessage;

    /**
     * Gets username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets username.
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns if the validation was successful or not.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * Sets validation to successful.
     */
    public function setSuccess()
    {
        $this->success = true;
    }

    /**
     * Sets validation to failure.
     *
     * @param string $message
     */
    public function setFailure($message = null)
    {
        $this->success = false;
        $this->failureMessage = $message;
    }

    /**
     * Gets the failure message.
     *
     * @return string
     */
    public function getFailureMessage()
    {
        return $this->failureMessage;
    }

}
