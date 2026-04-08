<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(FormationRepository $formations, EvenementRepository $evenements): Response
    {
        return $this->render('home/index.html.twig', [
            'formations_count' => count($formations->findAll()),
            'evenements_count' => count($evenements->findAll()),
        ]);
    }
}
