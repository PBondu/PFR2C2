<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MongoDBService;
use App\Repository\BillingRepository; 
use App\Repository\ContractRepository;  

 class DashboardController_old extends AbstractController
{
    private $entityManager;
    private $mongoDBService;

    public function __construct(EntityManagerInterface $entityManager, MongoDBService $mongoDBService)
    {
        $this->entityManager = $entityManager;
        $this->mongoDBService = $mongoDBService;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(BillingRepository $BillingRepository, ContractRepository $ContractRepository): Response
    {
        $mysqlData1 = $ContractRepository->findAll();
        $mysqlData2 = $BillingRepository->findAll();

        $mongoData1 = $this->mongoDBService->findAll('Customer', 'customers');
        $mongoData2 = $this->mongoDBService->findAll('Vehicle', 'vehicles');
        

        // return $this->render('dashboard/index.html.twig', [
        //     'mysqlData1' => $mysqlData1,
        //     'mysqlData2' => $mysqlData2,
        //     'mongoData1' => $mongoData1,
        //     'mongoData2' => $mongoData2,
        // ]);
    }
}
