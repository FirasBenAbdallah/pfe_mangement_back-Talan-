<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $notemoyenne = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'evaluations')] 
    private ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'evaluations')] 
    private ?Candidate $candidate = null;

    #[ORM\ManyToOne(targetEntity: EvaluationLine::class, inversedBy: 'evaluations')] 
    private ?EvaluationLine $evaluationline = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotemoyenne(): ?int
    {
        return $this->notemoyenne;
    }

    public function setNotemoyenne(int $notemoyenne): static
    {
        $this->notemoyenne = $notemoyenne;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getEvaluationLine(): ?EvaluationLine
    {
        return $this->evaluationline;
    }

    public function setEvaluationLine(?EvaluationLine $evaluationline): self
    {
        $this->evaluationline = $evaluationline;

        return $this;
    }


}