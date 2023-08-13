<?php

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\User;
use App\Entity\SchoolYear;
use App\Repository\SubjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/subjects')]
class SubjectController extends AbstractController
{
    #[Route('/', name: 'app_subject_index', methods: ['GET'])]
    public function index(SubjectRepository $subjectRepository): Response
    {
        $subjects = $subjectRepository->findAll();
        return $this->json($subjects, Response::HTTP_OK);

    }

    #[Route('', name: 'app_subject_new', methods: ['POST'])]
    public function new(Request $request,EntityManagerInterface $entityManager,SerializerInterface $serializer,ValidatorInterface $validator,): Response {
        $json = $request->getContent();
        $data = json_decode($json, true); // Decode JSON string to an associative array

        $subject = $serializer->deserialize($json, Subject::class, 'json');
        $errors = $validator->validate($subject);

        if (count($errors) === 0) {
            // Get the user ID from the JSON data.
            $userId = $data['user_id'] ?? null;
            // Get the user ID from the JSON data.
            $schoolyearId = $data['schoolyear_id'] ?? null;

            if ($userId) {
                // Find the user by its ID
                $user = $entityManager->getRepository(User::class)->find($userId);
                // Find the schoolyear by its ID
                $schoolyear = $entityManager->getRepository(SchoolYear::class)->find($schoolyearId);

                if (!$user) {
                    return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
                }
                // Assign the user to the subject
                $subject->setUser($user);
            } else {
                $subject->setUser(null);
            }
            if ($schoolyearId) {
                // Find the schoolyear by its ID
                $schoolyear = $entityManager->getRepository(SchoolYear::class)->find($schoolyearId);

                if (!$schoolyear) {
                    return $this->json(['error' => 'School year not found.'], Response::HTTP_NOT_FOUND);
                }
                // Assign the user to the subject
                $subject->setSchoolYear($schoolyear);
            } else {
                $subject->setSchoolYear(null);
            }

            $entityManager->persist($subject);
            $entityManager->flush();

            return $this->json($subject, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_subject_show', methods: ['GET'])]
    public function show(SchoolYear $schoolyear): Response
    {
        return $this->json($schoolyear, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_subject_edit', methods: ['PATCH'])]
    public function edit(Request $request, Subject $subject, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Update the user entity with the new data
        $subject->setLibelle($formData['libelle'] ?? $subject->getLibelle());
        $subject->setCompetences($formData['competences'] ?? $subject->getCompetences());
        $schoolyearId = $formData['schoolyear_id'];
        $userId = $formData['user_id'];

        // Update the subject's schoolyear
        $subject->setSchoolYear($entityManager->getRepository(SchoolYear::class)->find($schoolyearId));
        // Update the subject's user
        $subject->setUser($entityManager->getRepository(User::class)->find($userId));

        // Validate the updated user entity
        $errors = $validator->validate($subject);
        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();
            return $this->json(['message' => 'Subject updated successfully', $subject], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_subject_delete', methods: ['DELETE'])]
    public function delete(Request $request, Subject $subject, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($subject);
        $entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
