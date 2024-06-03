<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MongoDBService;
use App\Repository\ContractRepository;


#[Route('/vehicle', name: 'vehicle_')]
class VehicleController extends AbstractController
{
    private $mongoDBService;
    private $contractRepository;

    public function __construct(MongoDBService $mongoDBService, ContractRepository $contractRepository)
    {
        $this->mongoDBService = $mongoDBService;
        $this->contractRepository = $contractRepository;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();

        $sqlData = null;
        if ($request->isMethod('POST')) {
            $mongoId = $request->request->get('mongo_id');
            $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => (int)$mongoId]);

            if ($vehicle) {
                $vehicle_uid = $vehicle['_id']; 
                $sqlData = $this->contractRepository->findBy(['vehicle_uid' => $vehicle_uid]);
            }
        }

        return $this->render('vehicle/index.html.twig', [
            'vehicles' => $vehicles,
            'sqlData' => $sqlData,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data['_id'] = $this->mongoDBService->getNextSequence('vehicle_id');
            $this->mongoDBService->getDatabase('Vehicle')->vehicles->insertOne($data);
            return $this->redirectToRoute('vehicle_index');
        }
        return $this->render('vehicle/new.html.twig');
    }

    #[Route('/show/{id}', name: 'show')]
    public function show($id): Response
    {
        $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => (int)$id]);
        if (!$vehicle) {
            throw $this->createNotFoundException('The vehicle does not exist');
        }
        return $this->render('vehicle/show.html.twig', ['vehicle' => $vehicle]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id): Response
    {
        $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => (int)$id]);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase('Vehicle')->vehicles->updateOne(['_id' => (int)$id], ['$set' => $data]);
            return $this->redirectToRoute('vehicle_index');
        }
        return $this->render('vehicle/edit.html.twig', ['vehicle' => $vehicle]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id): Response
    {
        $this->mongoDBService->getDatabase('Vehicle')->vehicles->deleteOne(['_id' => (int)$id]);
        return $this->redirectToRoute('vehicle_index');
    }
}
