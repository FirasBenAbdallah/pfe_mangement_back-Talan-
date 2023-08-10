<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Nom obligatoire')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Prénom obligatoire')]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Email obligatoire')]
    #[Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Mot de passe obligatoire')]
    #[Assert\Length(min :4,max : 8,minMessage :"Mot de passe doit être au moins {{ limit }} characters long",maxMessage : "Mot de passe ne peut pas dépasser {{ limit }} characters")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Champ obligatoire')]
    private ?string $role = null;

    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'user')] 
    private $subjects;

    #[ORM\OneToMany(targetEntity: EvaluationLine::class, mappedBy: 'user')] 
    private $evaluationlines;

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'user')] 
    private $evaluations;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
