<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function redirectToDashboard(): RedirectResponse
    {
        return $this->redirectToRoute('dashboard_index');
    }

    #[Route('/dashboard', name: 'dashboard_index')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }
}
