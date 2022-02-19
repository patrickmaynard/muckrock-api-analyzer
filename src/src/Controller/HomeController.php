<?php

namespace App\Controller;

use App\Repository\RankingRepository;
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

        return [
            'rankings' => $rankings,
        ];
    }
}
