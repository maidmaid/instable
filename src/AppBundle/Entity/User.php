<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User
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
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="bio", type="text", nullable=true)
     */
    private $bio;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_picture", type="string", length=255)
     */
    private $profilePicture;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255, nullable=true)
     */
    private $fullName;

    /**
     * @var integer
     *
     * @ORM\Column(name="external_id", type="string", length=31)
     */
    private $externalId;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_media", type="integer", nullable=true)
     */
    private $countMedia;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_follows", type="integer", nullable=true)
     */
    private $countFollows;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_followed_by", type="integer", nullable=true)
     */
    private $countFollowedBy;

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set bio
     *
     * @param string $bio
     * @return User
     */
    public function setBio($bio)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string 
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set profilePicture
     *
     * @param string $profilePicture
     * @return User
     */
    public function setProfilePicture($profilePicture)
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    /**
     * Get profilePicture
     *
     * @return string 
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string 
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set externalId
     *
     * @param integer $externalId
     * @return User
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId
     *
     * @return integer 
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set countMedia
     *
     * @param integer $countMedia
     * @return User
     */
    public function setCountMedia($countMedia)
    {
        $this->countMedia = $countMedia;

        return $this;
    }

    /**
     * Get countMedia
     *
     * @return integer 
     */
    public function getCountMedia()
    {
        return $this->countMedia;
    }

    /**
     * Set countFollows
     *
     * @param integer $countFollows
     * @return User
     */
    public function setCountFollows($countFollows)
    {
        $this->countFollows = $countFollows;

        return $this;
    }

    /**
     * Get countFollows
     *
     * @return integer 
     */
    public function getCountFollows()
    {
        return $this->countFollows;
    }

    /**
     * Set countFollowedBy
     *
     * @param integer $countFollowedBy
     * @return User
     */
    public function setCountFollowedBy($countFollowedBy)
    {
        $this->countFollowedBy = $countFollowedBy;

        return $this;
    }

    /**
     * Get countFollowedBy
     *
     * @return integer 
     */
    public function getCountFollowedBy()
    {
        return $this->countFollowedBy;
    }
}
