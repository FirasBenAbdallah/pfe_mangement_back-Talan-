<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/users')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json($users, Response::HTTP_OK);
    }

    #[Route('', name: 'app_user_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $json = $request->getContent();
        $user = $serializer->deserialize($json, User::class, 'json');
        
        $errors = $validator->validate($user);
        if (count($errors) === 0) {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json($user, Response::HTTP_CREATED);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{prenom}/{nom}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_user_edit', methods: ['PATCH'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Update the user entity with the new data
        $user->setNom($formData['nom'] ?? $user->getNom());
        $user->setPrenom($formData['prenom'] ?? $user->getPrenom());
        $user->setEmail($formData['email'] ?? $user->getEmail());
        $user->setPassword($formData['password'] ?? $user->getPassword());
        $user->setRole($formData['role'] ?? $user->getRole());

        // Validate the updated user entity
        $errors = $validator->validate($user);

        if (count($errors) === 0) {
            // Save the changes to the database
            $entityManager->flush();

            return $this->json(['message' => 'User updated successfully', $user], Response::HTTP_OK);
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/login', name: 'app_user_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        // Get the JSON data from the request body
        $json = $request->getContent();
        $formData = json_decode($json, true);

        // Find the user by email using Symfony's UserRepository
        $user = $userRepository->findOneBy(['email' => $formData['email']]);

        // If the user exists and the password matches
        if ($user instanceof User && $this->isPasswordValid($user, $formData['password'])) {
            // Handle successful login, for example, return a success JSON response
            return $this->json(['message' => $user], Response::HTTP_OK);
        }

        // Handle failed login, for example, return an error JSON response
        return $this->json(['error' => 'Invalid email or password.'], Response::HTTP_UNAUTHORIZED);
    }

    private function isPasswordValid(User $user, string $password): bool
    {
        return $user->getPassword() === $password;
    }
}
