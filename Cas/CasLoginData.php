<?php

namespace Pucs\CasAuthBundle\Cas;

/**
 * Class CasLoginData
 */
class CasLoginData
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
     * Returns if user login successful or not.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * Sets user login to successful.
     */
    public function setSuccess()
    {
        $this->success = true;
    }

    /**
     * Sets user login to failure, along with a message.
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
