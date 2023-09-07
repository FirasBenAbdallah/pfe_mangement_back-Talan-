<?php

namespace App\Controller;

use App\Entity\EvaluationLine;
use App\Entity\User;
use App\Entity\Evaluation;
use App\Repository\EvaluationLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\EventListener\EvaluationLineListener;
use Doctrine\ORM\Event\LifecycleEventArgs;

#[Route('/evaluation/lines')]
class EvaluationLineController extends AbstractController
{
    #[Route('/', name: 'app_evaluation_line_index', methods: ['GET'])]
    public function index(EvaluationLineRepository $evaluationlineRepository): Response
    {
        $evaluationlines = $evaluationlineRepository->findAll();
        return $this->json($evaluationlines, Response::HTTP_OK);
    }

    #[Route('', name: 'app_evaluation_line_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $json = $request->getContent();
        $data = json_decode($json, true); // Decode JSON data into an array

        // Set default values for notes and reamrque
        $data['note1'] = 0;
        $data['note2'] = 0;
        $data['note3'] = 0;
        $data['note4'] = 0;
        $data['remarque'] = '';

        // Serialize the modified data
        $json = json_encode($data);

        $evaluationline = $serializer->deserialize($json, EvaluationLine::class, 'json');
        $errors = $validator->validate($evaluationline);

        if (count($errors) === 0) {
            $userId = $data['user_id'] ?? null;
            $evaluationId = $data['evaluation_id'] ?? null;
            if ($userId) {
                // Find the user by its ID
                $user = $entityManager->getRepository(User::class)->find($userId);

                if (!$user) {
                    return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
                }
                // Assign the user to the subject
                $evaluationline->setUser($user);
            } else {
                $evaluationline->setUser(null);
            }
            if ($evaluationId) {
                // Find the schoolyear by its ID
                $evaluation = $entityManager->getRepository(Evaluation::class)->find($evaluationId);

                if (!$evaluation) {
                    return $this->json(['error' => 'Evaluation not found.'], Response::HTTP_NOT_FOUND);
                }
                // Assign the user to the subject
                $evaluationline->setEvaluation($evaluation);
            } else {
                $evaluationline->setEvaluation(null);
            }

            $entityManager->persist($evaluationline);
            $entityManager->flush();

            return $this->json($evaluationline, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_evaluation_line_show', methods: ['GET'])]
    public function show(EvaluationLine $evaluationline): Response
    {
        return $this->json($evaluationline, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_evaluation_line_edit', methods: ['PATCH'])]
    public function edit(Request $request, EvaluationLine $evaluationline, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, EvaluationLineListener $evaluationLineListener): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Update the user entity with the new data
        $evaluationline->setNote1($formData['note1'] ?? $evaluationline->getNote1());
        $evaluationline->setNote2($formData['note2'] ?? $evaluationline->getNote2());
        $evaluationline->setNote3($formData['note3'] ?? $evaluationline->getNote3());
        $evaluationline->setNote4($formData['note4'] ?? $evaluationline->getNote4());
        $evaluationline->setRemarque($formData['remarque'] ?? $evaluationline->getRemarque());

        // Validate the updated user entity
        $errors = $validator->validate($evaluationline);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();

            // Trigger the event listener manually
            $eventArgs = new LifecycleEventArgs($evaluationline, $entityManager);
            $evaluationLineListener->preUpdate($evaluationline, $eventArgs);

            return $this->json(['message' => 'EvaluationLine updated successfully', $evaluationline], Response::HTTP_OK);
        }
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_evaluation_line_delete', methods: ['DELETE'])]
    public function delete(Request $request, EvaluationLine $evaluationline, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($evaluationline);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
