<?php

namespace App\Command;

use App\Entity\MajorCity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MajorCitiesLoadCommand extends Command
{
    protected static $defaultName = 'app:major-cities:load';
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Imports JSON for major city URLs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filePath = getcwd() . DIRECTORY_SEPARATOR . 'cities.json';

        $cities = json_decode(file_get_contents($filePath));

        foreach ($cities as $city) {
            $majorCity = new MajorCity;
            $majorCity->setName($city->name);
            $majorCity->setAbsoluteUrl($city->absolute_url);
            $this->entityManager->persist($majorCity);
        }

        $this->entityManager->flush();

        $io->success('Cities imported from cities.json.');

        return 0;
    }
}
