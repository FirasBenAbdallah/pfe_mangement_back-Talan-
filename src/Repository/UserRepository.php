<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Team as TeamEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // UserRepository.php or relevant service method
    public function findEncadrantUsers()
    {
        return $this->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'encadrant')
            ->getQuery()
            ->getResult();
    }

    // UserRepository.php or relevant service method
    public function findEvaluateurUsers()
    {
        return $this->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'evaluateur')
            ->getQuery()
            ->getResult();
    }

    public function findTeamsAssignedToUser(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->select('t')
            ->from('TeamEntity', 't')
            ->join('t.subject', 'c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
