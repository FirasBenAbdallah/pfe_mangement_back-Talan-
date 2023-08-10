<?php

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Libelle obligatoire')]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Compétences obligatoire')]
    private ?string $competences = null;

    #[ORM\ManyToOne(targetEntity: SchoolYear::class, inversedBy: 'subjects')] 
    private ?SchoolYear $schoolyear = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'subjects')] 
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'subject')] 
    private $teams;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCompetences(): ?string
    {
        return $this->competences;
    }

    public function setCompetences(string $competences): static
    {
        $this->competences = $competences;

        return $this;
    }

    public function getSchoolYear(): ?SchoolYear
    {
        return $this->schoolyear;
    }

    public function setSchoolYear(?SchoolYear $schoolyear): self
    {
        $this->schoolyear = $schoolyear;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
