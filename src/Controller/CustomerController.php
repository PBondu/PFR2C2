<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MongoDBService;
use App\Repository\ContractRepository;

#[Route('/customer', name: 'customer_')]
class CustomerController extends AbstractController
{
    private $mongoDBService;
    private $contractRepository;

    public function __construct(MongoDBService $mongoDBService, ContractRepository $contractRepository)
    {
        $this->mongoDBService = $mongoDBService;
        $this->contractRepository = $contractRepository;
    }

    // Route en annotations
    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $customers = $this->mongoDBService->getDatabase('Customer')->customers->find()->toArray();

        // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
        $sqlData = null;
        // Condition pour vérifier que la méthode HTTP est POST
        if ($request->isMethod('POST')) {
          // Récupère l'input utilisateur et le stocke dans une variable
            $mongoId = $request->request->get('mongo_id');
          // Récupère la db grace au service et cherche l'objet concerné par l'id saisi ci-dessus
            $customer = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['_id' => (int)$mongoId]);
          // Si l'objet est trouvé
            if ($customer) {
              // Transfère l'objet de l'array vers une variable
                $customer_uid = $customer['_id']; 
              // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
                $sqlData = $this->contractRepository->findBy(['customer_uid' => $customer_uid]);
            }
        }
        // Render des variables pour afficahge dans le twig
        return $this->render('customer/index.html.twig', [
            'customers' => $customers, // Affichage de base
            'sqlData' => $sqlData, // Affichage de la recherche utilisateur
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data['_id'] = $this->mongoDBService->getNextSequence('customer_id'); 
            $this->mongoDBService->getDatabase('Customer')->customers->insertOne($data);
            return $this->redirectToRoute('customer_index');
        }
        return $this->render('customer/new.html.twig');
    }

    #[Route('/show/{id}', name: 'show')]
    public function show($id): Response
    {
        $customer = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['_id' => (int)$id]);
        if (!$customer) {
            throw $this->createNotFoundException('The customer does not exist');
        }
        return $this->render('customer/show.html.twig', ['customer' => $customer]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id): Response
    {
        $customer = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['_id' => (int)$id]);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->mongoDBService->getDatabase('Customer')->customers->updateOne(['_id' => (int)$id], ['$set' => $data]);
            return $this->redirectToRoute('customer_index');
        }
        return $this->render('customer/edit.html.twig', ['customer' => $customer]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id): Response
    {
        $this->mongoDBService->getDatabase('Customer')->customers->deleteOne(['_id' => (int)$id]);
        return $this->redirectToRoute('customer_index');
    }
}
