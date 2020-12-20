<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MajorCityRepository")
 */
class MajorCity
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
    private $name;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $absoluteUrl;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $averageResponseTime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $successRate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastUpdate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAbsoluteUrl(): ?string
    {
        return $this->absoluteUrl;
    }

    public function setAbsoluteUrl(string $absoluteUrl): self
    {
        $this->absoluteUrl = $absoluteUrl;

        return $this;
    }

    public function getAverageResponseTime(): ?int
    {
        return $this->averageResponseTime;
    }

    public function setAverageResponseTime(?int $averageResponseTime): self
    {
        $this->averageResponseTime = $averageResponseTime;

        return $this;
    }

    public function getSuccessRate(): ?float
    {
        return $this->successRate;
    }

    public function setSuccessRate(?float $successRate): self
    {
        $this->successRate = $successRate;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }
}
