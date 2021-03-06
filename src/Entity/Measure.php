<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasureRepository")
 */
class Measure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
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
}
