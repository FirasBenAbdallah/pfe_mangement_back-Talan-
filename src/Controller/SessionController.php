<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\Team;
use App\Entity\Evaluation;
use App\Entity\Candidate;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\SessionService;
use App\Repository\EvaluationLineRepository;
use App\Repository\CandidateRepository;

#[Route('/sessions')]
class SessionController extends AbstractController
{
    private $sessionService;
    private $evaluationLineRepository;

    public function __construct(SessionService $sessionService, EvaluationLineRepository $evaluationLineRepository) {
        $this->sessionService = $sessionService;
        $this->evaluationLineRepository = $evaluationLineRepository;
    }

    #[Route('/', name: 'app_session_index', methods: ['GET'])]
    public function index(SessionRepository $sessionRepository): Response{
        $sessions = $sessionRepository->findAll();
        return $this->json($sessions, Response::HTTP_OK);
    }

    #[Route('', name: 'app_session_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, TeamRepository $teamRepository, UserRepository $userRepository, EvaluationController $evaluationController, EvaluationLineController $evaluationLineController, CandidateRepository $candidateRepository): Response {
        $data = json_decode($request->getContent(), true); // Decode JSON string to an associative array

        // Set statut to true
        $data['statut'] = true;
        // Extract selected user data from the request (adjust this part based on your frontend form structure)
        $selectedUserIds = $data['selectedUserIds'] ?? []; // Default to an empty array if not provided

        // Check if session_id and candidate_id are provided
        if (!isset($data['session_id'])) {
            return $this->json(['error' => 'Session ID and Candidate ID are required.'], Response::HTTP_BAD_REQUEST);
        }
        $session = $serializer->deserialize(json_encode($data), Session::class, 'json');
        $errors = $validator->validate($session);

        // Extract year from datedebut
        $datedebut = new \DateTime($data['datedebut']);
        $schoolYear = $datedebut->format('Y');

        if (count($errors) === 0) {
            $entityManager->persist($session);
            $sessionData = [
                'id' => $session->getId(),
                'name' => $session->getLibelle(),
            ];
            // Call the performRepartition function and pass the selected user data
            $performRepartitionResponse = $this->sessionService->performRepartition($schoolYear, $selectedUserIds, $teamRepository, $userRepository);
            // Combine session and teams/users data
            $repartitionData = json_decode($performRepartitionResponse->getContent(), true);
            $combinedData = [
                'session' => $sessionData, // Include the session data
                'repartitionData' => $repartitionData,
            ];
            $candidatesInTeams = $candidateRepository->findCandidatesInAllTeamsForSchoolYear($schoolYear);

            // Loop through the repartitionData and create EvaluationLine for each candidate
            foreach ($candidatesInTeams as $candidate) {
                // Create a new Evaluation using EvaluationController
                $evaluationRequest = new Request([], [], [], [], [], [], json_encode([
                    'session_id' => $session->getId(),
                    'candidate_id' => $candidate->getId(), // Use the ID of the candidate
                ]));
                $evaluationResponse = $evaluationController->new($evaluationRequest, $entityManager, $serializer, $validator, $this->evaluationLineRepository);
                // Create a new EvaluationLine for each assigned user
                foreach ($selectedUserIds as $selectedUserId) {
                    // Check if this user is assigned to the candidate's team
                    $assignedToCandidate = false;
                    foreach ($repartitionData['repartitionData'] as $teamData) {
                        if ($teamData['teamId'] === $candidate->getTeam()->getId()) {
                            foreach ($teamData['assignedUsers'] as $assignedUser) {
                                if ($assignedUser['userId'] === $selectedUserId) {
                                    $assignedToCandidate = true;
                                    break;
                                }
                            }
                        }
                        if ($assignedToCandidate) {
                            break;
                        }
                    }
                    if ($assignedToCandidate) {
                        $evaluationLineRequest = new Request([], [], [], [], [], [], json_encode([
                            'user_id' => $selectedUserId, // Adjust as needed
                            'evaluation_id' => json_decode($evaluationResponse->getContent(), true)['id'], // Use the ID of the created evaluation
                        ]));
                        $evaluationLineResponse = $evaluationLineController->new($evaluationLineRequest, $entityManager, $serializer, $validator);
                    }
                }
            }

            $entityManager->flush();
            return new JsonResponse($combinedData, Response::HTTP_CREATED);
        }
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response {
        return $this->json($session, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['PATCH'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response {
        // Get the JSON data from the request body
        $formData = json_decode($request->getContent(), true);

        // Update the user entity with the new data
        $session->setLibelle($formData['libelle'] ?? $session->getLibelle());
        $session->setDatedebut($formData['datedebut'] ?? $session->getDatedebut());
        $session->setDatefin($formData['datefin'] ?? $session->getDatefin());
        $session->setStatut($formData['statut'] ?? $session->getStatut());

        // Validate the updated user entity
        $errors = $validator->validate($session);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();

            return $this->json(['message' => 'Session updated successfully', $session], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_session_delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response {
        $entityManager->remove($session);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}


