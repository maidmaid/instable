<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TrackingRepository")
 */
class Tracking
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="tracker_id", referencedColumnName="id")
     */
    private $tracker;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="tracked_id", referencedColumnName="id")
     */
    private $tracked;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tracker
     *
     * @param \AppBundle\Entity\User $tracker
     * @return Tracking
     */
    public function setTracker(\AppBundle\Entity\User $tracker = null)
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * Get tracker
     *
     * @return \AppBundle\Entity\User 
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * Set tracked
     *
     * @param \AppBundle\Entity\User $tracked
     * @return Tracking
     */
    public function setTracked(\AppBundle\Entity\User $tracked = null)
    {
        $this->tracked = $tracked;

        return $this;
    }

    /**
     * Get tracked
     *
     * @return \AppBundle\Entity\User 
     */
    public function getTracked()
    {
        return $this->tracked;
    }
}
