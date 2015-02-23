<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

class InstableEvent extends GenericEvent
{
    protected $user;

    public function __construct($user, $subject = null, array $arguments = array())
    {
        parent::__construct($subject, $arguments);
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
