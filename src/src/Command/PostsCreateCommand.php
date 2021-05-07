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
    public static $defaultName = 'app:posts:create';

    public const FIRST_ENDPOINT = 'https://www.muckrock.com/api_v1/jurisdiction/?format=json&page=1';

    protected const RATE_LIMIT_DELAY = 1;

    protected const CONNECT_TIMEOUT_TIME = 120;

    protected $entityManager;

    protected $majorCityRepository;

    protected $nextPageEndpoint;

    /**
     * Constructor
     *
     * PostsCreateCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->majorCityRepository = $this
            ->entityManager
            ->getRepository(MajorCity::class)
        ;
        parent::__construct();
    }

    /**
     * Cofigure description, options
     */
    protected function configure()
    {
        $description = 'Downloads MuckRock data for major American cities';
        $this
            ->setDescription($description)
            ->addOption('show-urls', null, InputOption::VALUE_NONE, 'Whether to show urls')
            ->addOption('resume-from-url', null, InputOption::VALUE_REQUIRED, 'Which url to resume scraping from')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws MissingCitiesException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $this->checkForMissingCitiesException();

        while ($this->isOutdated()) {
            $page = $this->getNextEndpointAsObj($input);
            if (!$page) {
                break;
            }
            foreach ($page->results as $jurisdiction) {
                $majorCity = $this->majorCityRepository
                    ->findOneBy(['absoluteUrl' => $jurisdiction->absolute_url])
                ;
                if ($majorCity !== NULL) {
                    echo $jurisdiction->name . ' ... ';
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

                    //Doing a flush() inside of a loop is usually not a great idea.
                    //But we have a rate-limit delay anyway, so it's harmless here.
                    //Also, it allows our isOutdated() method to work correctly.
                    $this->entityManager->flush();
                    $counter++;
                }
                $this->nextPageEndpoint = $page->next;
            }
        }

        $this->analyzeAndCreatePost();

        $io->success('Download/analysis of major city data is complete.');

        return 0;
    }

    /**
     * @param InputInterface $input
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Made this public so it could be unit tested.
     * Most other functions use the database and so can't be unit tested.
     * So they remain protected or private.
     *
     */
    public function getNextEndpointAsObj(InputInterface $input)
    {
        sleep(self::RATE_LIMIT_DELAY);

        $client = new \GuzzleHttp\Client();
        if ($this->nextPageEndpoint === NULL) {
            $resumeFrom = $input->getOption('resume-from-url');
            if (!empty($resumeFrom)) {
                $this->nextPageEndpoint = $resumeFrom;
            } else {
                $this->nextPageEndpoint = self::FIRST_ENDPOINT;
            }
        }

        if ($input->getOption('show-urls')) {
            echo PHP_EOL . $this->nextPageEndpoint . PHP_EOL;
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

    /**
     * Is the scraped data in our database outdated?
     * @return bool
     */
    protected function isOutdated()
    {
        $nextOutdatedRecord = $this->majorCityRepository->getNextUnfilled();
        if (empty($nextOutdatedRecord)) {
            return false;
        }
        return true;
    }

    /**
     * Create the actual post
     * @throws \Exception
     */
    protected function analyzeAndCreatePost()
    {

        //$worstResponseTimeCities = $this->majorCityRepository->getWorstResponseTimeCities();
        //$worstSuccessRateCities = $this->majorCityRepository->getWorstSuccessRateCities();
        $bestResponseTimeCities = $this->majorCityRepository->getBestResponseTimeCities();
        $bestSuccessRateCities = $this->majorCityRepository->getBestSuccessRateCities();
        $post = new Post;
        $now = new \DateTime;
        $todayString = $now->format('Y-m-d');
        $post->setName($todayString);

        $content = '';

        $content .= '<h3>Ranked by average response times (best first)</h3>';
        foreach ($bestResponseTimeCities as $city) {
            /* @var MajorCity $city */
            $content .= $city->getName()
                . ' - '
                . $city->getAverageResponseTime()
                . ' days<br>'
            ;
        }

        $content .= '<h3>Ranked by success rates (best first)</h3>';
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

    /**
     * Do we have any metro areas to check?
     *
     * @throws MissingCitiesException
     */
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
