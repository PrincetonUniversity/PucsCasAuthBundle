<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Event;

use Pucs\CasAuthBundle\Cas\CasLoginData;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var UserInterface
     */
    protected $user;

    /**
     * @param CasLoginData  $casLoginData
     * @param UserInterface $user
     */
    public function __construct(CasLoginData $casLoginData, UserInterface $user)
    {
        $this->casLoginData = $casLoginData;
        $this->user = $user;
    }

    /**
     * @return CasLoginData
     */
    public function getCasLoginData()
    {
        return $this->casLoginData;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
