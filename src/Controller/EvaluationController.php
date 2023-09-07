<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Session;
use App\Entity\Candidate;
use App\Entity\EvaluationLine;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\EvaluationLineRepository;

#[Route('/evaluations')]
class EvaluationController extends AbstractController
{
    #[Route('/', name: 'app_evaluation_index', methods: ['GET'])]
    public function index(EvaluationRepository $evaluationRepository): Response {
        $evaluations = $evaluationRepository->findAll();
        return $this->json($evaluations, Response::HTTP_OK);
    }

    #[Route('', name: 'app_evaluation_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, EvaluationLineRepository $evaluationLineRepository): Response {
        $json = $request->getContent();
        $data = json_decode($json, true);
        $evaluation = $serializer->deserialize($json, Evaluation::class, 'json');
        $evaluation->setNotemoyenne(0);
        $errors = $validator->validate($evaluation);

        if (count($errors) === 0) {
            $sessionId = $data['session_id'] ?? null;
            $candidateId = $data['candidate_id'] ?? null;

            if ($sessionId && $candidateId) {
                $session = $entityManager->getRepository(Session::class)->find($sessionId);
                $candidate = $entityManager->getRepository(Candidate::class)->find($candidateId);

                if (!$session || !$candidate) {
                    return $this->json(['error' => 'Session or Candidate not found.'], Response::HTTP_NOT_FOUND);
                }
                $evaluation->setSession($session);
                $evaluation->setCandidate($candidate);

                $entityManager->persist($evaluation);
                $entityManager->flush();

                return $this->json($evaluation, Response::HTTP_CREATED);
            } else {
                return $this->json(['error' => 'Session ID or Candidate ID missing.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_evaluation_edit', methods: ['PATCH'])]
    public function edit(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, EvaluationLineRepository $evaluationLineRepository): Response {
        $json = $request->getContent();
        $formData = json_decode($json, true);
        $evaluationId = $evaluation->getId();
        $notemoyenne = $evaluationLineRepository->calculateAverageNoteForEvaluation($evaluationId);
        $evaluation->setNotemoyenne($notemoyenne);
        $errors = $validator->validate($evaluation);

        if (count($errors) === 0) {
            $entityManager->flush();
            return $this->json(['message' => 'Evaluation updated successfully', $evaluation], Response::HTTP_OK);
        }
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_evaluation_delete', methods: ['DELETE'])]
    public function delete(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response {
        $entityManager->remove($evaluation);
        $entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
