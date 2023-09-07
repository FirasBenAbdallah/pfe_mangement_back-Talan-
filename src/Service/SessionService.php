<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class SessionService
{
    private $entityManager;
    private $teamRepository;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, TeamRepository $teamRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
    }

    public function performRepartition(int $schoolYear, array $selectedUserIds, TeamRepository $teamRepository, UserRepository $userRepository): Response {
        $teams = $teamRepository->findTeamsBySchoolYear($schoolYear);
        $selectedUsers = $userRepository->findBy(['id' => $selectedUserIds]);
    
        if (count($teams) === 0 || empty($selectedUsers)) {
            return new JsonResponse(['message' => 'No teams available for repartition'], Response::HTTP_BAD_REQUEST);
        }
    
        $repartitionData = [];
    
        // Calculate the number of users per team
        $usersPerTeam = intdiv(count($selectedUsers), count($teams));
        $usersLeft = count($selectedUsers) % count($teams); // Calculate the remaining users
    
        // Create an associative array to store users by team ID
        $usersByTeam = [];
    
        foreach ($teams as $team) {
            // Initialize the assignedUsers array for this team
            $usersByTeam[$team->getId()] = [
                'teamId' => $team->getId(),
                'teamName' => $team->getNom(),
                'assignedUsers' => [],
            ];
        }
    
        // Sort users randomly
        // shuffle($selectedUsers);
    
        foreach ($teams as $team) {
            // Calculate the number of users to assign to this team
            $usersToAssign = $usersPerTeam;
    
            // If there are remaining users, assign one to this team
            if ($usersLeft > 0) {
                $usersToAssign += 1;
                $usersLeft -= 1;
            }
    
            // Assign users to the team
            while ($usersToAssign > 0 && count($selectedUsers) > 0) {
                $user = array_shift($selectedUsers);
    
                // Check if the user is already assigned to the team's subject
                if (!$this->isUserAssignedToTeamSubject($user, $team, $this->teamRepository)) {
                    $userId = $user->getId();
                    $userName = $user->getNom() . ' ' . $user->getPrenom();
    
                    // Add the user to the team's assignedUsers
                    $usersByTeam[$team->getId()]['assignedUsers'][] = [
                        'userId' => $userId,
                        'userName' => $userName,
                    ];
    
                    $usersToAssign -= 1;
                }
            }
        }
    
        // Convert the associative array to a simple array
        $repartitionData = array_values($usersByTeam);
    
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Repartition completed successfully', 'repartitionData' => $repartitionData]);
    }

    private function repartitionUsersByTeams(array $users, array $teams, TeamRepository $teamRepository): array {
        $repartitionData = [];
        
        // Shuffle both users and teams arrays
        shuffle($users);
        shuffle($teams);
    
        // Calculate the average number of users per team
        $averageUsersPerTeam = count($users) / count($teams);
        
        // If $averageUsersPerTeam is greater than 2, set it to 2
        if ($averageUsersPerTeam > 2) {
            $averageUsersPerTeam = 2;
        }
    
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
                if (!$this->isUserAssignedToTeamSubject($user, $team, $this->teamRepository)) {
                    $teamData['assignedUsers'][] = [
                        'userId' => $user->getId(),
                        'userName' => $user->getNom() . ' ' . $user->getPrenom(),
                    ];
                }
            }
    
            $repartitionData[] = $teamData;
        }
        
        return $repartitionData;
    }
    
    private function isUserAssignedToTeamSubject(User $user, Team $team, TeamRepository $teamRepository): bool
    {
        // Get the subject associated with the team
        $subject = $team->getSubject();
        
        // Use the custom repository method to check if the user is assigned to the subject
        return $teamRepository->isUserAssignedToSubject($user, $subject);
    }

}
