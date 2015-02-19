<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\RelationshipRepository")
 */
class Relationship
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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="target_user_id", referencedColumnName="id")
     */
    private $targetUser;

    /**
     * @var string
     *
     * @ORM\Column(name="followed", type="boolean")
     */
    private $followed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    public function __construct($user, $targetUser, $followed, $createdAt, $updatedAt)
    {
        $this->user = $user;
        $this->targetUser = $targetUser;
        $this->followed = $followed;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set followed.
     *
     * @param boolean $followed
     *
     * @return Relationship
     */
    public function setFollowed($followed)
    {
        $this->followed = $followed;

        return $this;
    }

    /**
     * Get followed.
     *
     * @return boolean
     */
    public function getFollowed()
    {
        return $this->followed;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Relationship
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return Relationship
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Relationship
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set targetUser.
     *
     * @param \AppBundle\Entity\User $targetUser
     *
     * @return Relationship
     */
    public function setTargetUser(\AppBundle\Entity\User $targetUser = null)
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    /**
     * Get targetUser.
     *
     * @return \AppBundle\Entity\User
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }
}
