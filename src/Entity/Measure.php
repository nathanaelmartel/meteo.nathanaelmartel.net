<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasureRepository")
 */
class Measure
{
    public const TYPES = [
        'panneau-solaire' => 'Panneau Solaire',
        'eau' => 'Eau',
        'voiture' => 'Voiture',
    ];

    public const TYPESICONS = [
        'panneau-solaire' => 'solar-panel',
        'eau' => 'faucet-drip',
        'voiture' => 'car',
    ];

    public const UNIT = [
        'panneau-solaire' => 'kWH',
        'eau' => 'm3',
        'voiture' => '€',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $measured_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $stated_at;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $releve;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getIcon(): ?string
    {
        if (isset(self::TYPESICONS[$this->getType()])) {
            return self::TYPESICONS[$this->getType()];
        }

        return '';
    }

    public function getTypeLabel(): ?string
    {
        if (isset(self::TYPES[$this->getType()])) {
            return self::TYPES[$this->getType()];
        }

        return $this->getType();
    }

    public function getUnit(): ?string
    {
        if (isset(self::UNIT[$this->getType()])) {
            return self::UNIT[$this->getType()];
        }

        return '';
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getMeasuredAt(): ?\DateTimeInterface
    {
        return $this->measured_at;
    }

    public function setMeasuredAt(\DateTimeInterface $measured_at): self
    {
        $this->measured_at = $measured_at;

        return $this;
    }

    public function getStatedAt(): ?\DateTimeInterface
    {
        return $this->stated_at;
    }

    public function setStatedAt(?\DateTimeInterface $stated_at): self
    {
        $this->stated_at = $stated_at;

        return $this;
    }

    public function getReleve(): ?float
    {
        return $this->releve;
    }

    public function setReleve(?float $releve): self
    {
        $this->releve = $releve;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }
}
