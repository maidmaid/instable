<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    public function findOneByExternalId($externalId)
    {
        return parent::findOneBy(array('externalId' => $externalId));
    }
}
