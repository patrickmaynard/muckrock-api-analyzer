<?php

namespace App\Command;

use App\Entity\MajorCity;
use App\Entity\Ranking;
use App\Repository\MajorCityRepository;
use App\Repository\RankingRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RankingsCreateCommand extends Command
{
    private const MUCKROCK_JURISDICTION_URI = 'https://www.muckrock.com/api_v1/jurisdiction/';
    private const PAGE_SIZE = 10;

    private Client $client;
    private RankingRepository $rankingRepository;
    private MajorCityRepository $majorCitiesRepository;
    private EntityManagerInterface $manager;

    public function __construct(
        RankingRepository $rankingRepository,
        MajorCityRepository $majorCitiesRepository,
        EntityManagerInterface $manager
    ) {
        parent::__construct();
        $this->client                = new Client();
        $this->rankingRepository     = $rankingRepository;
        $this->majorCitiesRepository = $majorCitiesRepository;
        $this->manager               = $manager;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:rankings:create')
            ->setDescription('')
            ->addOption(
                'runAgain',
                'r',
                InputOption::VALUE_NONE,
                'If you need to resume importing use this option to override check for last run.'
            )
            ->addOption(
                'startPage',
                's',
                InputOption::VALUE_REQUIRED,
                'References the Muckrock api page to start importing from. Default: 1',
                1
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $lastRanking = $this->rankingRepository->findOneBy([], ['date' => 'DESC']);
        $lastUpdated = is_null($lastRanking)
            ? new DateTime('yesterday')
            : $lastRanking->getDate();
        $today       = new DateTime('today');

        if ($lastUpdated >= $today && !$input->getOption('runAgain')) {
            $io->writeln(
                'Rankings are up to date. Update process stopped.',
                OutputInterface::VERBOSITY_NORMAL
            );
            exit(0);
        }

        try {
            $maxItems = $this->getMaxItemCount();
        } catch (JsonException|GuzzleException $e) {
            $io->error($e->getMessage());
            exit(1);
        }

        if ($maxItems === 0) {
            $io->writeln(
                'Found 0 rankings to update. Update process stopped.',
                OutputInterface::VERBOSITY_NORMAL
            );
            exit(0);
        }

        $io->writeln(
            sprintf(
                'Found %d items. Start updating.',
                $maxItems
            ),
            OutputInterface::VERBOSITY_NORMAL
        );

        $rankedCities = $this->getRankedCities();

        $requestUri = self::MUCKROCK_JURISDICTION_URI . '?' . \http_build_query(
                ['format' => 'json', 'page' => 1, 'page_size' => self::PAGE_SIZE]
            );

        $ranking = $input->getOption('runAgain')
            ? $this->rankingRepository->findOneBy(['date' => $today])
            : new Ranking()
        ;
        $cities  = $ranking->getCities() ?? [];

        $io->progressStart($maxItems);

        do {
            try {
                $responseObj = $this->fetchRankingsFromApi($requestUri);
            } catch (JsonException|GuzzleException $e) {
                $io->error($e->getMessage());
                exit(1);
            }

            if (empty($responseObj->results)) {
                continue;
            }

            $io->progressAdvance(count($responseObj->results));

            foreach ($responseObj->results as $city) {
                if (!in_array($city->absolute_url, $rankedCities, true)) {
                    continue;
                }

                $cities[] = [
                    'name'          => $city->name,
                    'response_time' => $city->average_response_time,
                    'success_rate'  => $city->success_rate,
                ];
            }


            $requestUri = $responseObj->next;

            if (is_null($requestUri)) {
                break;
            }

            $maxItems -= self::PAGE_SIZE;
        } while ($maxItems > 0);
        $io->progressFinish();

        $ranking
            ->setDate($today)
            ->setCities($cities)
        ;

        $this->manager->persist($ranking);
        $this->manager->flush();

        exit(0);
    }

    /**
     * @return int
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function getMaxItemCount(): int
    {
        $options = [
            'query' => [
                'format'    => 'json',
                'page'      => 1,
                'page_size' => 1,
            ],
        ];

        $responseBody = $this->client->get(self::MUCKROCK_JURISDICTION_URI, $options)->getBody()->getContents();

        return json_decode($responseBody, false, 512, JSON_THROW_ON_ERROR)->count ?? 0;
    }

    /**
     * @return array
     */
    protected function getRankedCities(): array
    {
        $rankedCities = $this->majorCitiesRepository->findAll();

        $rankedCities = array_map(static function (MajorCity $city) {
            return $city->getAbsoluteUrl();
        }, $rankedCities);
        sort($rankedCities);

        return $rankedCities;
    }

    /**
     * @param string $requestUri
     *
     * @return object
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function fetchRankingsFromApi(string $requestUri): object
    {
        $responseContents = $this->client->get($requestUri)->getBody()->getContents();

        return json_decode($responseContents, false, 512, JSON_THROW_ON_ERROR);
    }
}
