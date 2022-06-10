<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Repository\RankingRepository;
use phpDocumentor\Reflection\Types\Static_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @Template
     */
    public function index(RankingRepository $rankingRepository): array
    {
        $rankings = $rankingRepository->findBy([], ['date' => 'DESC'], 10);

        $rankings = array_map(static function (Ranking $ranking) {
            $cities = $ranking->getCities();

            usort($cities, static function (array $a, array $b): int {
                return $a['response_time'] <=> $b['response_time'];
            });

            return $ranking->setCities($cities);
        }, $rankings);

        return [
            'rankings' => $rankings,
        ];
    }
}
