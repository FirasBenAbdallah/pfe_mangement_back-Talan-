<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/teams')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAll();
        return $this->json($teams, Response::HTTP_OK);
    }

    #[Route('', name: 'app_team_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $json = $request->getContent();
        $team = $serializer->deserialize($json, Team::class, 'json');
        
        $errors = $validator->validate($team);
        if (count($errors) === 0) {
            $entityManager->persist($team);
            $entityManager->flush();
            return $this->json($team, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->json($team, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_team_edit', methods: ['PATCH'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Update the user entity with the new data
        $team->setNom($formData['nom'] ?? $team->getNom());
        $team->setTaille($formData['taille'] ?? $team->getTaille());

        // Validate the updated user entity
        $errors = $validator->validate($team);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();

            return $this->json(['message' => 'Team updated successfully', $team], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }
}