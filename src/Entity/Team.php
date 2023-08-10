<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank (message:'Nom obligatoire')]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank (message:'Taille obligatoire')]
    #[Assert\Positive(message:"Taille doit étre positive")]
    private ?int $taille = null;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'teams')] 
    private ?Subject $subject = null;

    #[ORM\OneToMany(targetEntity: Candidate::class, mappedBy: 'team')]
    private $candidates;

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

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}
