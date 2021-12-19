<?php

namespace Ij\SyliusByjunoPlugin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ij\SyliusByjunoPlugin\Entity\ByjunoLog;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @method ByjunoLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ByjunoLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ByjunoLog[]    findAll()
 * @method ByjunoLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ByjunoLogRepository extends EntityRepository implements RepositoryInterface
{

    // /**
    //  * @return ByjunoLogTest[] Returns an array of ByjunoLogTest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ByjunoLogTest
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
