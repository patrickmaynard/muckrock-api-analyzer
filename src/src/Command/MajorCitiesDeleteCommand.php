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

class MajorCitiesDeleteCommand extends Command
{
    protected static $defaultName = 'app:major-cities:delete';
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes all major cities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->removeAllCities();

        $io->success($count . ' major cities have been deleted.');
        $io->success('To reload JSON, use the app:major-cities:load command.');

        return 0;
    }

    protected function removeAllCities()
    {
        $majorCityRepository = $this
            ->entityManager
            ->getRepository(MajorCity::class)
        ;
        $cities = $majorCityRepository->findAll();
        $count = count($cities);
        foreach ($cities as $city) {
            $this->entityManager->remove($city);
        }
        $this->entityManager->flush();
        return $count;
    }
}
