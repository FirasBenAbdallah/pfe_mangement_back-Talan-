<?php

namespace App\Controller;

use App\Entity\SchoolYear;
use App\Repository\SchoolYearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/school/years')]
class SchoolYearController extends AbstractController
{
    #[Route('/', name: 'app_school_year_index', methods: ['GET'])]
    public function index(SchoolYearRepository $schoolyearRepository): Response
    {
        $schoolyears = $schoolyearRepository->findAll();
        return $this->json($schoolyears, Response::HTTP_OK);
    }

    #[Route('', name: 'app_school_year_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $schoolyear = $serializer->deserialize($request->getContent(), SchoolYear::class, 'json');
        
        $errors = $validator->validate($schoolyear);
        if (count($errors) === 0) {
            $entityManager->persist($schoolyear);
            $entityManager->flush();
            return $this->json($schoolyear, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_school_year_show', methods: ['GET'])]
    public function show(SchoolYear $schoolyear): Response
    {
        return $this->json($schoolyear, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_school_year_edit', methods: ['PATCH'])]
    public function edit(Request $request, SchoolYear $schoolyear, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $formData = json_decode($request->getContent(), true);

        // Convert the 'datedebut' and 'datefin' strings to DateTime objects
        $schoolyear->setAnnee($formData['annee'] ?? $schoolyear->getAnnee());

        // Validate the updated user entity
        $errors = $validator->validate($schoolyear);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();
            return $this->json(['message' => 'School year updated successfully', $schoolyear], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_school_year_delete', methods: ['DELETE'])]
    public function delete(Request $request, SchoolYear $schoolyear, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($schoolyear);
        $entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
