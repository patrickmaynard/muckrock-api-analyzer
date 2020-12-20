<?php

namespace App\Command;

use App\Entity\MajorCity;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadMajorCityDataCommand extends Command
{
    protected static $defaultName = 'app:download-major-city-data';
    protected const FIRST_ENDPOINT = 'https://www.muckrock.com/api_v1/jurisdiction/?format=json&page=1';
    protected const RATE_LIMIT_DELAY = 2;
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

        $counter = 1;
        while ($this->getNextEndpointAsObj() && $this->isOutdated()) {
            $page = $this->getNextEndpointAsObj();
            sleep(self::RATE_LIMIT_DELAY);
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
                    $this->nextPageEndpoint = $page->next;
                    $counter++;
                    if ($counter === self::NUMBER_OF_CITIES) {
                        $this->entityManager->flush();
                        break(2);
                    }
                }
            }
        }

        $this->analyzeAndCreatePost();

        $io->success('Download/analysis of major city data is complete.');

        return 0;
    }

    protected function getNextEndpointAsObj()
    {
        if ($this->nextPageEndpoint === NULL) {
            $this->nextPageEndpoint = self::FIRST_ENDPOINT;
        }

        if ($this->nextPageEndpoint === NULL) {
            return false;
        }

        $asObj = json_decode(file_get_contents($this->nextPageEndpoint));
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
        //$this->removeAllPosts();
        $worstResponseTimeCities = $this->majorCityRepository->getWorstResponseTimeCities();
        $worstSuccessRateCities = $this->majorCityRepository->getWorstSuccessRateCities();
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


        $post->setContent($content);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    protected function removeAllPosts()
    {
        $postRepository = $this
            ->entityManager
            ->getRepository(Post::class)
        ;
        $posts = $postRepository->findAll();
        foreach ($posts as $post) {
            $this->entityManager->remove($post);
        }
        $this->entityManager->flush();
    }
}
