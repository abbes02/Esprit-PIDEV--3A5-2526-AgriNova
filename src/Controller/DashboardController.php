<?php

namespace App\Controller;

use App\Service\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(UserContext $userContext): Response
    {
        $currentUser = $userContext->requireCurrentUser();

        return $this->redirectToRoute('app_materiel_index', ['user_id' => $currentUser->getId()]);
    }
}
