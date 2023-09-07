<?php

namespace App\Repository;

use App\Entity\EvaluationLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvaluationLine>
 *
 * @method EvaluationLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationLine[]    findAll()
 * @method EvaluationLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationLine::class);
    }

    // Custom method to calculate the average note
    public function calculateAverageNoteForEvaluation(int $evaluationId): ?float
    {
        return $this->createQueryBuilder('el')
            ->select('AVG((el.note1 + el.note2 + el.note3 + el.note4) / 4) as average_note')
            ->andWhere('el.evaluation = :evaluationId')
            ->setParameter('evaluationId', $evaluationId)
            ->getQuery()
            ->getSingleScalarResult();
    }
//    /**
//     * @return EvaluationLine[] Returns an array of EvaluationLine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EvaluationLine
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
