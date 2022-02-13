<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RankingRepository")
 */
class Ranking
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTime $date;

    /**
     * @var array|null
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $cities;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return Ranking
     */
    public function setDate(DateTime $date): Ranking
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCities(): ?array
    {
        return $this->cities;
    }

    /**
     * @param array|null $cities
     *
     * @return Ranking
     */
    public function setCities(?array $cities): Ranking
    {
        $this->cities = $cities;

        return $this;
    }
}
