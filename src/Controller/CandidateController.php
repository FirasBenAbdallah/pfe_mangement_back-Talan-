<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/candidates')]
class CandidateController extends AbstractController
{
    #[Route('/', name: 'app_candidate_index', methods: ['GET'])]
    public function index(CandidateRepository $candidateRepository): Response
    {
        $candidates = $candidateRepository->findAll();
        return $this->json($candidates, Response::HTTP_OK);

    }

    #[Route('', name: 'app_candidate_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $json = $request->getContent();
        $candidate = $serializer->deserialize($json, Candidate::class, 'json');
        
        $errors = $validator->validate($candidate);
        if (count($errors) === 0) {
            $entityManager->persist($candidate);
            $entityManager->flush();
            return $this->json($candidate, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate): Response
    {
        return $this->json($candidate, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_candidate_edit', methods: ['PATCH'])]
    public function edit(Request $request, Candidate $candidate, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Update the user entity with the new data
        $candidate->setNom($formData['nom'] ?? $candidate->getNom());
        $candidate->setPrenom($formData['prenom'] ?? $candidate->getPrenom());
        $candidate->setEmail($formData['email'] ?? $candidate->getEmail());
        $candidate->setNumtel($formData['numtel'] ?? $candidate->getNumtel());

        // Convert the 'datedebut' and 'datefin' strings to DateTime objects
        $datedebutStr = $formData['datedebut'] ?? $candidate->getDatedebut();
        $datedebut = new \DateTime($datedebutStr);
        $candidate->setDatedebut($datedebut);

        $datefinStr = $formData['datefin'] ?? $candidate->getDatefin();
        $datefin = new \DateTime($datefinStr);
        $candidate->setDatefin($datefin);

        // Validate the updated user entity
        $errors = $validator->validate($candidate);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();

            return $this->json(['message' => 'Candidate updated successfully', $candidate], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_candidate_delete', methods: ['DELETE'])]
    public function delete(Request $request, Candidate $candidate, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($candidate);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}