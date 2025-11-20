<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\Table(name: 'candidature')]
#[ORM\HasLifecycleCallbacks]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Le nom et prénom sont obligatoires")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nomPrenom = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire")]
    #[Assert\Regex(
        pattern: "/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/",
        message: "Le numéro de téléphone n'est pas valide"
    )]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Le poste recherché est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le poste recherché doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le poste recherché ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $posteRecherche = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: "La prétention salariale est obligatoire")]
    private ?string $pretentionSalariale = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: "La disponibilité est obligatoire")]
    #[Assert\Choice(
        choices: ['immediatement', '1mois', '2mois', '3mois', 'autre'],
        message: "La disponibilité sélectionnée n'est pas valide"
    )]
    private ?string $disponibilite = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Le CV est obligatoire")]
    private ?string $cv = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $statut = 'nouvelle'; // nouvelle, en_cours, retenue, refusee, archivee

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomPrenom(): ?string
    {
        return $this->nomPrenom;
    }

    public function setNomPrenom(string $nomPrenom): static
    {
        $this->nomPrenom = $nomPrenom;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPosteRecherche(): ?string
    {
        return $this->posteRecherche;
    }

    public function setPosteRecherche(string $posteRecherche): static
    {
        $this->posteRecherche = $posteRecherche;
        return $this;
    }

    public function getPretentionSalariale(): ?string
    {
        return $this->pretentionSalariale;
    }

    public function setPretentionSalariale(string $pretentionSalariale): static
    {
        $this->pretentionSalariale = $pretentionSalariale;
        return $this;
    }

    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(string $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): static
    {
        $this->cv = $cv;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    /**
     * Get disponibilite label
     */
    public function getDisponibiliteLabel(): string
    {
        return match($this->disponibilite) {
            'immediatement' => 'Immédiatement',
            '1mois' => 'Dans 1 mois',
            '2mois' => 'Dans 2 mois',
            '3mois' => 'Dans 3 mois',
            'autre' => 'Autre',
            default => $this->disponibilite
        };
    }
}