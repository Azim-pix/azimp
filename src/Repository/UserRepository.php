<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Constant\CardStatus;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }


    //---------------------------------------
    // Users
    //---------------------------------------

    public function searchUsers($filters = [], $page = 1, $pageSize = 10, $orderBy = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->select(["u"])
        ;

        foreach($orderBy as $order){
            $qb = $qb->addOrderBy('u.'.$order[0], $order[1]);
        }

        $qb = $this->_applyFilters($qb, $filters);

        $qb = $qb
            ->setFirstResult($pageSize * ($page-1))
            ->setMaxResults($pageSize)
            ->getQuery()->getResult();

        return $qb;
    }

    public function countUsers($filters = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->select("COUNT(DISTINCT u.id)")
        ;

        $qb = $this->_applyFilters($qb, $filters);

        $qb = $qb
            ->getQuery()
            ->getSingleScalarResult();

        return $qb;
    }

    public function searchUserById($id)
    {
        $qb = $this->createQueryBuilder('u')
            ->select(["u", "c", "p", "r", "category"])
            ->leftJoin('u.links', 'c')
            ->leftJoin('u.favoriteCategories', 'category')
            ->innerJoin('u.pixie', 'p')
            ->innerJoin('p.regions', 'r')
            ->where("u.id = :id")->setParameter("id", $id)
        ;

        $qb = $qb->getQuery()
            ->getOneOrNullResult();

        return $qb;
    }

    public function searchUserFollowingPixies()
    {
        $qb = $this->createQueryBuilder('u')
            ->select(["u"])
            ->innerJoin('u.favoritePixies', 'user_pixies')
        ;

        $qb = $qb->getQuery()
            ->getResult();

        return $qb;
    }


    //---------------------------------------
    // Pixies
    //---------------------------------------

    public function searchPixies($filters = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->select(["u", "avatar", "c", "p", "r", "category"])
            ->leftJoin('u.avatar', 'avatar')
            ->leftJoin('u.cards', 'cards')
            ->leftJoin('u.links', 'c')
            ->leftJoin('u.favoriteCategories', 'category')
            ->innerJoin('u.pixie', 'p')
                ->innerJoin('p.regions', 'r')

            ->where('u.deleted IS NULL OR u.deleted = 0')
            ->andWhere('u.visible = 1')
            ;

        $qb = $this->_applyFilters($qb, $filters);

        $qb = $qb->getQuery()->getResult();

        return $qb;
    }

    public function findByPixieRegion($regionId)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.avatar', 'avatar')
            ->innerJoin('u.pixie', 'p')
            ->innerJoin('p.regions', 'r')
            ->where('r.id = :region OR r.slug = :region')
            ->andWhere('u.deleted IS NULL OR u.deleted = 0')
            ->andWhere('u.visible = 1')
            ->setParameter('region', $regionId)
            ->getQuery()
            ->getResult()
            ;
    }


    public function countPixieByRegion($regionId)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->innerJoin('u.pixie', 'p')
            ->innerJoin('p.regions', 'r')
            ->where('r.id = :region')->setParameter('region', $regionId)
            ->andWhere('u.deleted IS NULL OR u.deleted = 0')
            ->andWhere('u.visible = 1')
            ->getQuery()
            ->useResultCache(true, 0, "countPixieByRegion_".$regionId)
            ->getSingleScalarResult()
            ;
    }

    public function findRandomPixies($regionId = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->select(["u", "c", "p", "r", "RAND() as HIDDEN rand"])
						->join('u.cards','ca')
            ->leftJoin('u.links', 'c')
            ->innerJoin('u.pixie', 'p')
                ->innerJoin('p.regions', 'r')
            ->where('u.deleted IS NULL OR u.deleted = 0')
						->where('ca.status = :status')->setParameter('status',CardStatus::VALIDATED)
            ->andWhere('u.visible = 1')
        ;

        if($regionId){
            $qb = $qb->andWhere("r.id = :region")->setParameter("region", $regionId);
        }

        return $qb
            ->groupBy('u.id')
            ->orderBy('rand')
            ->setMaxResults(5)
            ->getQuery()
            ->useResultCache(true, 0, "findRandomPixies")
            ->getResult()
            ;
    }

    private function _applyFilters($qb, $filters){
        if($filters) {
            if (isset($filters["text"])) {
                $qb = $qb->andWhere("CONCAT(u.firstname, ' ', u.lastname) LIKE :searchText")->setParameter("searchText", "%".$filters["text"]."%");
            }

            if (isset($filters["regions"])) {
                $qb = $qb->andWhere("r IN (:regions) OR r.slug IN (:regions)")->setParameter("regions", $filters["regions"]);
            }

            if (isset($filters["categories"])) {
                $qb = $qb->andWhere("category.id IN (:categories) OR category.slug IN (:categories)")->setParameter("categories", $filters["categories"]);
            }

            if (isset($filters["pixies"])) {
                $qb = $qb->andWhere("u.id IN (:pixies)")->setParameter("pixies", $filters["pixies"]);
            }

            if (isset($filters["pixie"])) {
                $qb = $qb->andWhere("u.id = :pixie")->setParameter("pixie", $filters["pixie"]);
            }
        }

        return $qb;
    }

}