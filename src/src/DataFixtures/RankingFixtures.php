<?php

namespace App\DataFixtures;

use App\Entity\Ranking;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class RankingFixtures extends Fixture implements FixtureGroupInterface
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $january = (new Ranking())
            ->setDate(new DateTime('2022-01-28'))
            ->setCities(
                [
                    ['name' => 'Detroit', 'response_time' => 10, 'success_rate' => 30.555555555556],
                    ['name' => 'Arlington', 'response_time' => 37, 'success_rate' => 41.818181818182],
                    ['name' => 'Tulsa', 'response_time' => 25, 'success_rate' => 11.688311688312],
                ]
            )
        ;
        $manager->persist($january);

        $february = (new Ranking())
            ->setDate(new DateTime('2022-02-28'))
            ->setCities(
                [
                    ['name' => 'Detroit', 'response_time' => 10, 'success_rate' => 30.555555555556],
                    ['name' => 'Indianapolis', 'response_time' => 39, 'success_rate' => 31.818181818182],
                    ['name' => 'Tulsa', 'response_time' => 25, 'success_rate' => 11.688311688312],
                ]
            )
        ;

        $manager->persist($february);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['ranking'];
    }
}
