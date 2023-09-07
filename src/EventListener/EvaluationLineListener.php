<?php

// src/EventListener/EvaluationLineListener.php
namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\EvaluationLine;

class EvaluationLineListener
{
    public function preUpdate(EvaluationLine $evaluationLine, LifecycleEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $evaluation = $evaluationLine->getEvaluation();

        $notemoyenne = $entityManager
            ->getRepository(EvaluationLine::class)
            ->calculateAverageNoteForEvaluation($evaluation->getId());

        $evaluation->setNotemoyenne($notemoyenne);
        $entityManager->persist($evaluation);
        $entityManager->flush();
    }
}

