<?php

namespace App\Controller;

use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/search')]
class SearchController extends AbstractController
{
  // Route en annotations
  #[Route('/', name: 'app_search_index', methods: ['GET'])]
  public function index(ContractRepository $contractRepository, Request $request, BillingRepository $billingRepository): Response
  {
    /*
        RECHERCHE BILLING A PARTIR ID CONTRAT
      */
    // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
    $billingById = null;
    // Condition pour vérifier que la méthode HTTP est POST
    if ($request->isMethod('POST')) {
      // Récupère l'input utilisateur et le stocke dans une variable
      $id_from_input = $request->request->get('billing_id');
      // Cherche l'objet concerné par l'id saisi ci-dessus
      $contractArrayBill = $contractRepository->findBy(array('id' => (int)$id_from_input));
      // Si l'objet est trouvé
      if ($contractArrayBill) {
        // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
        $billingById = $billingRepository->findBy(['Contract_id' => $contractArrayBill]);
      }
    }
    /*
        FIN RECHERCHE BILLING
      */

    /*
        RECHERCHE CONTRACT A PARTIR ID CONTRAT
      */
    // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
    $contractById = null;
    // Condition pour vérifier que la méthode HTTP est POST
    if ($request->isMethod('POST')) {

      // Récupère l'input utilisateur et le stocke dans une variable
      $id_from_input = $request->request->get('contract_id');
      // Cherche l'objet concerné par l'id saisi ci-dessus
      $contractArray = $contractRepository->findBy(array('id' => (int)$id_from_input));
      // Si l'objet est trouvé
      if ($contractArray) {
        // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
        $contractById = $contractArray;
      }
    }
    /*
        FIN RECHERCHE CONTRACT
    */

    /*
        CONTRATS IMPAYES
    */
    $unpayed = $request->request->get('unpayed');
    $unpayedContracts = [];

    if ($unpayed == 'show') {
      
      foreach ($contractRepository->findAll() as $cont) {
        $compare = $billingRepository->findBy(['Contract_id' => $cont]);

        if ($compare == []) {
          $unpayedContracts[] = $cont;
        }
      }  
    }
    else{
      $unpayedContracts = [];
    }
    /*
        FIN CONTRATS IMPAYES
    */

    /*
        CONTRATS RETARDS
    */
    $late_input = $request->request->get('late');
    $lateContracts = [];

    if ($late_input == 'show') {
      
      foreach ($contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
          $lateContracts[] = $cont;
        }
      }  
    }
    else{
      $lateContracts = [];
    }
    /*
        FIN CONTRATS RETARDS
    */
    /*
        CONTRATS EN COURS
    */
    $current_contract_input = $request->request->get('current');
    $currentContract = [];

    if ($current_contract_input == 'show') {
      
      foreach ($contractRepository->findAll() as $cont) {
        if ($cont->returning_datetime == null) {
          $currentContract[] = $cont;
        }
      }  
    }
    else{
      $currentContract = [];
    }
    /*
        FIN CONTRATS EN COURS
    */

    // Render des variables pour afficahge dans le twig
    return $this->render('search/index.html.twig', [
      'billingById' => $billingById, // Affichage de la recherche utilisateur
      'contractById' => $contractById,
      'unpayedContracts' => $unpayedContracts,
      'lateContracts' => $lateContracts,
      'currentContract' => $currentContract,
    ]);
  }
}