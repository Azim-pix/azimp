<?php

namespace App\Repository;

use App\Entity\UserPacks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserPacks|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPacks|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPacks[]    findAll()
 * @method UserPacks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPacksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserPacks::class);
    }

    // /**
    //  * @return UserPacks[] Returns an array of UserPacks objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserPacks
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}