<?php

namespace App\Entity;

use App\Repository\SchoolYearRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchoolYearRepository::class)]
class SchoolYear
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Assert\Range(min: 1000, max: 9999)]
    #[Assert\NotBlank (message:'Year field is required')]
    private ?int $annee = null;

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'schoolyear')] 
    private $sessions;

    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'schoolyear')] 
    private $subjects;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }
}
