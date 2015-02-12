<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RelationshipRepository
 */
class RelationshipRepository extends EntityRepository
{
    /**
     * @param $user
     * @return Relationship
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByLast($user, $targetUser)
    {
        $query = $this->createQueryBuilderByLast()
            ->andWhere('r.user = :user')
            ->andWhere('r.targetUser = :targetUser')
            ->setParameter('user', $user)
            ->setParameter('targetUser', $targetUser)
            ->getQuery();

        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function createQueryBuilderByLast()
    {
        $queryMax = $this->createQueryBuilder('r2')
            ->select('MAX(r2.id)')
            ->groupBy('r2.user')
            ->addGroupBy('r2.targetUser');

        return $this->createQueryBuilder('r')
            ->where(sprintf('r.id IN (%s)', $queryMax));
    }

    /**
     * @param $user User
     * @param $updateAt
     * @return Relationship[]
     */
    public function findByUserAndPreUpdatedAt($user, $updateAt)
    {
        $query = $this->createQueryBuilderByLast()
            ->andWhere('r.user = :user')
            ->andWhere('r.updatedAt < :updateAt')
            ->andWhere('r.followed = true')
            ->setParameter('user', $user)
            ->setParameter('updateAt', $updateAt)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param $targetUser User
     * @param $updateAt
     * @return Relationship[]
     */
    public function findByTargetUserAndPreUpdatedAt($targetUser, $updateAt)
    {
        $query = $this->createQueryBuilderByLast()
            ->andWhere('r.targetUser = :targetUser')
            ->andWhere('r.updatedAt < :updateAt')
            ->andWhere('r.followed = true')
            ->setParameter('targetUser', $targetUser)
            ->setParameter('updateAt', $updateAt)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * @return Relationship[]
     */
    public function findAllByUser($user)
    {
        $query = $this->createQueryBuilderByAll()
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * @return Relationship[]
     */
    public function findAllByTargetUser($targetUser)
    {
        $query = $this->createQueryBuilderByAll()
            ->where('r.targetUser = :targetUser')
            ->setParameter('targetUser', $targetUser)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    public function createQueryBuilderByAll()
    {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->join('r.targetUser', 'tu')
            ->orderBy('r.createdAt', 'DESC')
            ->addOrderBy('r.updatedAt', 'DESC');
    }
}
