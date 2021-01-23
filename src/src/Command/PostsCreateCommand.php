<?php

namespace App\Command;

use App\Entity\MajorCity;
use App\Entity\Post;
use App\Exception\MissingCitiesException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PostsCreateCommand extends Command
{
    protected static $defaultName = 'app:posts:create';
    protected const FIRST_ENDPOINT = 'https://www.muckrock.com/api_v1/jurisdiction/?format=json&page=1';
    protected const RATE_LIMIT_DELAY = 2;
    protected const CONNECT_TIMEOUT_TIME = 120;
    protected const NUMBER_OF_CITIES = 50;
    protected $entityManager;
    protected $majorCityRepository;
    protected $nextPageEndpoint;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->majorCityRepository = $this
            ->entityManager
            ->getRepository(MajorCity::class)
        ;
        parent::__construct();
    }

    protected function configure()
    {
        $description = 'Downloads MuckRock data for major American cities';
        $this->setDescription($description);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $this->checkForMissingCitiesException();

        $counter = 1; // A little weird to start on 1, but this gives us a nice visual counter :-)
        while ($this->isOutdated()) {
            $page = $this->getNextEndpointAsObj();
            if (!$page) {
                break;
            }
            foreach ($page->results as $jurisdiction) {
                $majorCity = $this->majorCityRepository
                    ->findOneBy(['absoluteUrl' => $jurisdiction->absolute_url])
                ;
                if ($majorCity !== NULL) {
                    echo $counter . '. ' . $jurisdiction->name . ' ... ';
                    /* @var MajorCity $majorCity */
                    $majorCity->setAverageResponseTime(
                        $jurisdiction->average_response_time
                    );
                    $majorCity->setSuccessRate(
                        $jurisdiction->success_rate
                    );
                    $majorCity->setLastUpdate(
                        new \DateTime()
                    );
                    if ($counter === self::NUMBER_OF_CITIES) {
                        $this->entityManager->flush();
                        break(2);
                    }
                    $counter++;
                }
                $this->nextPageEndpoint = $page->next;
            }
        }

        $this->analyzeAndCreatePost();

        $io->success('Download/analysis of major city data is complete.');

        return 0;
    }

    protected function getNextEndpointAsObj()
    {
        sleep(self::RATE_LIMIT_DELAY);

        $client = new \GuzzleHttp\Client();
        if ($this->nextPageEndpoint === NULL) {
            $this->nextPageEndpoint = self::FIRST_ENDPOINT;
        }
        $response = $client->request(
            'GET',
            $this->nextPageEndpoint,
            [
                'connect_timeout' => self::CONNECT_TIMEOUT_TIME,
            ]
        );
        $asObj = json_decode($response->getBody());

        return $asObj;
    }

    protected function isOutdated()
    {
        $nextOutdatedRecord = $this->majorCityRepository->getNextUnfilled();
        if (empty($nextOutdatedRecord)) {
            return false;
        }
        return true;
    }

    protected function analyzeAndCreatePost()
    {

        $worstResponseTimeCities = $this->majorCityRepository->getWorstResponseTimeCities();
        $worstSuccessRateCities = $this->majorCityRepository->getWorstSuccessRateCities();
        $bestResponseTimeCities = $this->majorCityRepository->getBestResponseTimeCities();
        $bestSuccessRateCities = $this->majorCityRepository->getBestSuccessRateCities();
        $post = new Post;
        $now = new \DateTime;
        $todayString = $now->format('Y-m-d');
        $post->setName($todayString);

        $content = '<h3>Worst average response times</h3>';
        foreach ($worstResponseTimeCities as $city) {
            /* @var MajorCity $city */
            $content .= $city->getName()
                . ' - '
                . $city->getAverageResponseTime()
                . ' days<br>'
            ;
        }

        $content .= '<h3>Worst success rates</h3>';
        foreach ($worstSuccessRateCities as $city) {
            /* @var MajorCity $city */
            $content .= $city->getName()
                . ' - '
                . $city->getSuccessRate()
                . '%<br>'
            ;
        }

        $content .= '<h3>Best average response times</h3>';
        foreach ($bestResponseTimeCities as $city) {
            /* @var MajorCity $city */
            $content .= $city->getName()
                . ' - '
                . $city->getAverageResponseTime()
                . ' days<br>'
            ;
        }

        $content .= '<h3>Best success rates</h3>';
        foreach ($bestSuccessRateCities as $city) {
            /* @var MajorCity $city */
            $content .= $city->getName()
                . ' - '
                . $city->getSuccessRate()
                . '%<br>'
            ;
        }


        $post->setContent($content);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    protected function checkForMissingCitiesException()
    {
        $cities = $this->majorCityRepository->findAll();
        if (count($cities) === 0) {
            throw new MissingCitiesException(
                'Please run app:major-cities:load to load cities.'
            );
        }
    }
}
