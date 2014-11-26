<?php

namespace Pucs\CasAuthBundle\Event;

use Pucs\CasAuthBundle\Cas\CasLoginData;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CasAuthenticationEvent
 */
class CasAuthenticationEvent extends Event
{
    /**
     * @var CasLoginData
     */
    protected $casLoginData;

    /**
     * @param CasLoginData $casLoginData
     */
    public function __construct(CasLoginData $casLoginData)
    {
        $this->casLoginData = $casLoginData;
    }

    /**
     * @return CasLoginData
     */
    public function getCasLoginData()
    {
        return $this->casLoginData;
    }
}
