<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MongoDBService;

#[Route('/vehicle', name: 'vehicle_')]
class VehicleController extends AbstractController
{
    private $mongoDBService;

    public function __construct(MongoDBService $mongoDBService)
    {
        $this->mongoDBService = $mongoDBService;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();
        return $this->render('vehicle/index.html.twig', ['vehicles' => $vehicles]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase('Vehicle')->vehicles->insertOne($data);
            return $this->redirectToRoute('vehicle_index');
        }
        return $this->render('vehicle/new.html.twig');
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id): Response
    {
        $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase('vehicle')->vehicles->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)], ['$set' => $data]);
            return $this->redirectToRoute('vehicle_index');
        }
        return $this->render('vehicle/edit.html.twig', ['vehicle' => $vehicle]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id): Response
    {
        $this->mongoDBService->getDatabase('Vehicle')->vehicles->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $this->redirectToRoute('vehicle_index');
    }
}
