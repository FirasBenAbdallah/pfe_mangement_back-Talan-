<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findTeamsBySchoolYear(int $schoolYear): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.subject', 's')
            ->innerJoin('s.schoolyear', 'sy')
            ->where('sy.annee = :schoolYear')
            ->setParameter('schoolYear', $schoolYear)
            ->getQuery()
            ->getResult();
    }

    public function isUserAssignedToSubject(User $user, Subject $subject): bool
    {
        // Example DQL query (adjust as needed):
        $query = $this->getEntityManager()->createQuery(
            'SELECT COUNT(t.id)
            FROM App\Entity\Team t
            JOIN t.subject s
            JOIN s.user u
            WHERE t.subject = :subject
            AND u = :user'
        );

        $query->setParameter('subject', $subject);
        $query->setParameter('user', $user);

        $result = $query->getSingleScalarResult();

        return $result > 0;
    }

//    /**
//     * @return Team[] Returns an array of Team objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
