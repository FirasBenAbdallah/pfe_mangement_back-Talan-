<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Lastname field is required')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Firstname field is required')]
    private ?string $prenom = null;


    #[ORM\Column(unique:true,length: 255)]
    #[Assert\NotBlank (message:'Email field is required')]
    #[Email(message: 'Email address {{ value }} is not valid.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    //#[Assert\NotBlank (message:'Password field is required')]
    #[Assert\Length(min :4,minMessage :"Password must be at least {{ limit }} characters long")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Role field is required')]
    private ?string $role = null;

    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'user')] 
    private $subjects;

    #[ORM\OneToMany(targetEntity: EvaluationLine::class, mappedBy: 'user')] 
    private $evaluationlines;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /* public function getRole(): ?string
    {
        return $this->role;
    } */
    // Implement the getRoles() method
    public function getRoles(): array
    {
        return [$this->role];
    }
    
    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }
    // Implement the getUsername() method (usually email is used as the username)
    public function getUsername(): string
    {
        return $this->email;
    }
    
    // Implement the getSalt() method (you can return null if not using plaintext passwords)
    public function getSalt(): ?string
    {
        // Return null if you're using a modern hashing algorithm like bcrypt
        return null;
    }

    // Implement the eraseCredentials() method (usually not needed for most cases)
    public function eraseCredentials()
    {
        // The plaintext password (if any) should not be persisted
        $this->plainPassword = null;
    }


    // Add a method to set and encrypt the password
    public function setPasswordAndEncrypt(string $password): self
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);

        return $this;
    }
}
