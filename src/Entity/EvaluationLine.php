<?php

namespace App\Entity;

use App\Repository\EvaluationLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationLineRepository::class)]
class EvaluationLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $note1 = null;

    #[ORM\Column]
    private ?int $note2 = null;

    #[ORM\Column]
    private ?int $note3 = null;

    #[ORM\Column]
    private ?int $note4 = null;

    #[ORM\Column(length: 255)]
    private ?string $remarque = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'evaluationlines')] 
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: 'evaluationlines')] 
    private ?Evaluation $evaluation = null;
   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote1(): ?int
    {
        return $this->note1;
    }

    public function setNote1(int $note1): static
    {
        $this->note1 = $note1;

        return $this;
    }

    public function getNote2(): ?int
    {
        return $this->note2;
    }

    public function setNote2(int $note2): static
    {
        $this->note2 = $note2;

        return $this;
    }

    public function getNote3(): ?int
    {
        return $this->note3;
    }

    public function setNote3(int $note3): static
    {
        $this->note3 = $note3;

        return $this;
    }

    public function getNote4(): ?int
    {
        return $this->note4;
    }

    public function setNote4(int $note4): static
    {
        $this->note4 = $note4;

        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(string $remarque): static
    {
        $this->remarque = $remarque;

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

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;

        return $this;
    }
}
