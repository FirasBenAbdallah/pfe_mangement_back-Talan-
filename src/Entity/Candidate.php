<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\CandidateRepository;
use Symfony\Component\Validator\Constraints\Email;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
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

    #[ORM\Column]
    #[Assert\NotBlank (message:'Phone number field is required')]
    #[Assert\Range(min :10000000,max : 99999999,minMessage :"Phone number must contain 8 digits", maxMessage :"Phone number must contain 8 digits")]
    private ?int $numtel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datedebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datefin = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'candidates')] 
    private ?Team $team = null;

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'candidate')] 
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

    public function getNumtel(): ?int
    {
        return $this->numtel;
    }

    public function setNumtel(int $numtel): static
    {
        $this->numtel = $numtel;

        return $this;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
