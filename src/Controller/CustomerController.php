<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MongoDBService;

#[Route('/customer', name: 'customer_')]
class CustomerController extends AbstractController
{
    private $mongoDBService;

    public function __construct(MongoDBService $mongoDBService)
    {
        $this->mongoDBService = $mongoDBService;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $customers = $this->mongoDBService->getDatabase()->customers->find()->toArray();
        return $this->render('customer/index.html.twig', ['customers' => $customers]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase()->customers->insertOne($data);
            return $this->redirectToRoute('customer_index');
        }
        return $this->render('customer/new.html.twig');
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id): Response
    {
        $customer = $this->mongoDBService->getDatabase()->customers->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase()->customers->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)], ['$set' => $data]);
            return $this->redirectToRoute('customer_index');
        }
        return $this->render('customer/edit.html.twig', ['customer' => $customer]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id): Response
    {
        $this->mongoDBService->getDatabase()->customers->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $this->redirectToRoute('customer_index');
    }
}
