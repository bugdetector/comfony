<?php

namespace App\Repository\Auth;

use App\Entity\Auth\ForgetPasswordToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForgetPasswordToken>
 *
 * @method ForgetPasswordToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForgetPasswordToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForgetPasswordToken[]    findAll()
 * @method ForgetPasswordToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForgetPasswordTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForgetPasswordToken::class);
    }

//    /**
//     * @return ForgetPasswordToken[] Returns an array of ForgetPasswordToken objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ForgetPasswordToken
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
