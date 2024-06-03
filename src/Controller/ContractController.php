<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BillingRepository;


#[Route('/contract')]
class ContractController extends AbstractController
{
    // Route en annotations
    #[Route('/', name: 'app_contract_index', methods: ['GET'])]
    public function index(ContractRepository $contractRepository, Request $request, BillingRepository $billingRepository): Response
    {
      // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
      $sqlData = null;
      // Condition pour vérifier que la méthode HTTP est POST
      if ($request->isMethod('POST')) {
          // Récupère l'input utilisateur et le stocke dans une variable
          $sqlId = $request->request->get('sql_id');
          // Cherche l'objet concerné par l'id saisi ci-dessus
          $contractArray = $contractRepository->findBy(array('id' => (int)$sqlId));
          // Si l'objet est trouvé
          if ($contractArray) {
              // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
              $sqlData = $billingRepository->findBy(['Contract_id' => $contractArray]);
          }
      }
        // Render des variables pour afficahge dans le twig
        return $this->render('contract/index.html.twig', [
          'contracts' => $contractRepository->findAll(),// Affichage de base
          'sqlData' => $sqlData, // Affichage de la recherche utilisateur
      ]);
    }

    #[Route('/new', name: 'app_contract_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contract = new Contract();
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contract);
            $entityManager->flush();

            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contract/new.html.twig', [
            'contract' => $contract,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show', methods: ['GET'])]
    public function show(Contract $contract): Response
    {
        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contract $contract, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contract/edit.html.twig', [
            'contract' => $contract,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($contract);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
    }
}
