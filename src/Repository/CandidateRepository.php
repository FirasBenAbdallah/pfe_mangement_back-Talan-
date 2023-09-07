<?php

namespace App\Repository;

use App\Entity\Candidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Candidate>
 *
 * @method Candidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Candidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Candidate[]    findAll()
 * @method Candidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function countCandidatesPerTeam(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('t.id AS teamId, COUNT(c.id) AS candidateCount')
            ->innerJoin('c.team', 't')
            ->groupBy('t.id');

        $results = $qb->getQuery()->getResult();

        $counts = [];

        foreach ($results as $result) {
            $teamId = $result['teamId'];
            $candidateCount = $result['candidateCount'];
            $counts[$teamId] = $candidateCount;
        }

        return $counts;
    }

    public function findCandidatesInAllTeamsForSchoolYear($schoolYear)
    {
        $qb = $this->_em->createQueryBuilder(); // Create a new QueryBuilder instance

        $qb->select('c')
            ->from(Candidate::class, 'c')
            ->join('c.team', 't') // Assuming 'team' is the property in Candidate entity that links to Team
            ->join('t.subject', 's') // Assuming 'subject' is the property in Team entity that links to Subject
            ->join('s.schoolyear', 'sy') // Assuming 'schoolyear' is the property in Subject entity that links to SchoolYear
            ->where($qb->expr()->eq('sy.annee', ':schoolYear'))
            ->setParameter('schoolYear', $schoolYear);

        return $qb->getQuery()->getResult();
    }

    public function countCandidatesInTeamsForSchoolYear($schoolYear)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('t.id AS teamId', 'COUNT(c.id) AS candidateCount')
            ->from(Team::class, 't')
            ->leftJoin('t.candidates', 'c')
            ->leftJoin('t.subject', 's')
            ->where($qb->expr()->eq('s.schoolyear', ':schoolYear'))
            ->groupBy('t.id')
            ->setParameter('schoolYear', $schoolYear);

        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Candidate[] Returns an array of Candidate objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Candidate
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
