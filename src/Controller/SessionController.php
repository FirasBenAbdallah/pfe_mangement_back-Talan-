<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\Team;
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

#[Route('/sessions')]
class SessionController extends AbstractController
{
    #[Route('/', name: 'app_session_index', methods: ['GET'])]
    public function index(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findAll();
        return $this->json($sessions, Response::HTTP_OK);
    }

    #[Route('', name: 'app_session_new', methods: ['POST'])]
    public function new(Request $request,EntityManagerInterface $entityManager,SerializerInterface $serializer,ValidatorInterface $validator,TeamRepository $teamRepository,UserRepository $userRepository): Response {
        $json = $request->getContent();
        $data = json_decode($json, true); // Decode JSON string to an associative array

        // Set statut to true
        $data['statut'] = true;

        // Update JSON string with modified data
        $json = json_encode($data);

        $session = $serializer->deserialize($json, Session::class, 'json');
        $errors = $validator->validate($session);

        // Extract year from datedebut
        $datedebut = new \DateTime($data['datedebut']);
        $schoolYear = $datedebut->format('Y');

        // Call the listTeamsAndUsers function and get its response
        $listTeamsAndUsersResponse = $this->forward('App\Controller\SessionController::listTeamsAndUsers', [
            'teamRepository' => $teamRepository,
            'userRepository' => $userRepository,
            'schoolYear' => $schoolYear,
        ]);
        // Get the content from the response
        $listTeamsAndUsersContent = $listTeamsAndUsersResponse->getContent();

        if (count($errors) === 0) {

            $entityManager->persist($session);
            $entityManager->flush();

            // Combine session and teams/users data
            $combinedData = [
                'session' => $session,
                'teamsAndUsers' => json_decode($listTeamsAndUsersContent, true),
            ];
            
            return new JsonResponse($combinedData, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->json($session, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['PATCH'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

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
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($session);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }


   // #[Route('/api/list-teams-and-users/{schoolYear}', name: 'api_list_teams_and_users',methods: ['GET'])]
    public function listTeamsAndUsers(TeamRepository $teamRepository,UserRepository $userRepository, int $schoolYear): JsonResponse
    {
        $users = $userRepository->findAll(); // Fetch all users
        $teams = $teamRepository->findTeamsBySchoolYear($schoolYear);

        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                // Add other user properties as needed
            ];
        }

        $teamsData = [];
        foreach ($teams as $team) {
            $subjectData = [
                'id' => $team->getSubject()->getId(),
                'libelle' => $team->getSubject()->getLibelle(),
                // Add other subject properties as needed
            ];

            $teamsData[] = [
                'id' => $team->getId(),
                'nom' => $team->getNom(),
                'taille' => $team->getTaille(),
                'subject' => $subjectData,
                // Add other team properties as needed
            ];
        }

        $data = [
            'users' => $usersData,
            'teams' => $teamsData,
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/repartition', name: 'app_repartition', methods: ['POST'])]
    public function performRepartition(TeamRepository $teamRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $schoolYear = 2025; // Set the desired school year
        $users = $userRepository->findAll();
        $teams = $teamRepository->findTeamsBySchoolYear($schoolYear);
        shuffle($users); // Shuffle the list of users
        shuffle($teams); // Shuffle the list of teams
        $repartitionData = [];

        // Calculate the average number of users per team
        $averageUsersPerTeam = count($users) / count($teams);

        foreach ($teams as $team) {
            $teamData = [
                'teamId' => $team->getId(),
                'teamName' => $team->getNom(),
                'assignedUsers' => []
            ];

            // Assign users to the team until it reaches the average
            while (count($teamData['assignedUsers']) < $averageUsersPerTeam && !empty($users)) {
                $user = array_shift($users);
                
                // Check if the user is already assigned to the team's subject
                if (!$this->isUserAssignedToTeamSubject($user, $team, $teamRepository)) {
                    $teamData['assignedUsers'][] = [
                        'userId' => $user->getId(),
                        'userName' => $user->getNom() . ' ' . $user->getPrenom(),
                    ];
                }
            }

            $repartitionData[] = $teamData;
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Repartition completed successfully', 'repartitionData' => $repartitionData]);
    }

    private function isUserAssignedToTeamSubject(User $user, Team $team, TeamRepository $teamRepository): bool
    {
        // Get the subject associated with the team
        $subject = $team->getSubject();

        // Use the custom repository method to check if the user is assigned to the subject
        return $teamRepository->isUserAssignedToSubject($user, $subject);
    }

}

