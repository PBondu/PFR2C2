<?php

namespace App\Controller;

use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;

#[Route('/search')]
class SearchController extends AbstractController
{

  private $mongoDBService;
  private $contractRepository;

  public function __construct(MongoDBService $mongoDBService, ContractRepository $contractRepository)
  {
      $this->mongoDBService = $mongoDBService;
      $this->contractRepository = $contractRepository;
  }

  // Route en annotations
  #[Route('/', name: 'app_search_index', methods: ['GET'])]
  public function index(MongoDBService $mongoDBService, ContractRepository $contractRepository, Request $request, BillingRepository $billingRepository): Response
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
        RECHERCHE CONTRACT BY CUSTOMER ID
    */

    // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
    $customerById = null;
    // Condition pour vérifier que la méthode HTTP est POST
    if ($request->isMethod('POST')) {
      // Récupère l'input utilisateur et le stocke dans une variable
      $customer_id_input = $request->request->get('customerId');
      // Récupère la db grace au service et cherche l'objet concerné par l'id saisi ci-dessus
      $customer = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['_id' => (int)$customer_id_input]);
      // Si l'objet est trouvé
      if ($customer) {
        // Transfère l'objet de l'array vers une variable
        $customer_uid = $customer['_id'];
        // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
        $customerById = $this->contractRepository->findBy(['customer_uid' => $customer_uid]);
      }
    }

    /*
      FIN RECHERCHE CONTRACT BY CUSTOMER ID
    */

    /*
        RECHERCHE CONTRACT BY VEHICLE ID
    */

    $vehicleById = null;
    if ($request->isMethod('POST')) {
        $vehicle_id_input = $request->request->get('vehicleId');
        $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => (int)$vehicle_id_input]);

        if ($vehicle) {
            $vehicle_uid = $vehicle['_id']; 
            $vehicleById = $this->contractRepository->findBy(['vehicle_uid' => $vehicle_uid]);
        }
    }

    /*
      FIN RECHERCHE CONTRACT BY VEHICLE ID
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
    } else {
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
    } else {
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
    } else {
      $currentContract = [];
    }
    /*
      FIN CONTRATS EN COURS
    */

    /*
     RETARDS MOYENS PAR CUSTOMER
    */
    $late_ave_input = $request->request->get('lateAverage');
    $latenessPerCustomer = 0;
    $AllCustomerArray = $this->mongoDBService->getDatabase('Customer')->customers->find()->toArray();
    $AllCustomerNumber = count($AllCustomerArray);
    if ($late_ave_input == 'show') {

      foreach ($contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
          ++$latenessPerCustomer;
        }
      }
      $latenessPerCustomer = $latenessPerCustomer / $AllCustomerNumber;
    }
    /*
      FIN RETARDS MOYENS PAR CUSTOMER
    */

    /*
     NOMBRE RETARDS ENTRE DATES INPUT
    */
    $lateness_begin_date_input = $request->request->get('beginDateLate');
    $lateness_end_date_input = $request->request->get('endDateLate');
    

    $latenessNumber = 0;

    if ($lateness_begin_date_input != null && $lateness_end_date_input != null) {
      $lateness_begin_date_input = strtotime($lateness_begin_date_input);
      $lateness_end_date_input = strtotime($lateness_end_date_input);
      
      
      foreach ($contractRepository->findAll() as $cont) {     

        if ($cont->locend_datetime < $cont->returning_datetime && $cont->returning_datetime->getTimestamp() > $lateness_begin_date_input
        && $lateness_end_date_input > $cont->returning_datetime->getTimestamp()) {
          ++$latenessNumber;
        }
      }
    }
    /*
      FIN NOMBRE RETARDS ENTRE DATES INPUT
    */

    /*
     TEMPS DE RETARD MOYENS PAR VEHICULE
    */
// Controller or service method
$time_late_ave_input = $request->request->get('timeLateAverage');
$averageLatenessPerVehicle = [];
$indicesOfAverageLateness = [];

if ($time_late_ave_input == 'show') {
    // Initialize an associative array to store lateness times per vehicle
    $vehicleLateness = [];

    // Loop through all contracts
    foreach ($contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
            // Calculate lateness time in seconds
            $latenessTime = $cont->returning_datetime->getTimestamp() - $cont->locend_datetime->getTimestamp();

            // Add lateness time to the array for this vehicle
            if (!isset($vehicleLateness[$cont->vehicle_uid])) {
                $vehicleLateness[$cont->vehicle_uid] = [];
            }
            $vehicleLateness[$cont->vehicle_uid][] = $latenessTime;
        }
    }

    // Calculate the average lateness time for each vehicle
    foreach ($vehicleLateness as $vehicleUid => $latenessTimes) {
        $totalLateness = array_sum($latenessTimes);
        $averageLateness = $totalLateness / count($latenessTimes);
        $days = floor($averageLateness / (60 * 60 * 24));
        $hours = floor(($averageLateness % (60 * 60 * 24)) / (60 * 60));
        $averageLatenessPerVehicle[$vehicleUid] = $days . ' jours et ' . $hours . ' heures';
    }

    // Collect the indices of the average lateness per vehicle
    foreach ($averageLatenessPerVehicle as $index => $value) {
        $indicesOfAverageLateness[] = $index;
    }
}



    // $time_late_ave_input = $request->request->get('timeLateAverage');
    // $latenessPerVehicle = [];
    // $latenessPerVehicleNumber = [];
    // $AllVehcileArray = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();
    // $AllVehicleNumber = count($AllVehcileArray);

    // if ($time_late_ave_input == 'show') {
    //   foreach ($contractRepository->findAll() as $cont) {
    //     if ($cont->locend_datetime < $cont->returning_datetime) {
    //       $lateness_time_calcul = $cont->returning_datetime->getTimestamp() - $cont->locend_datetime->getTimestamp();
    //       $lateness_time_dateFormat = date('d' ,$lateness_time_calcul). ' jours et ' . date('H',$lateness_time_calcul) . ' heures';
    //       $latenessPerVehicleNumber[] = $cont->vehicle_uid;
    //       $latenessPerVehicle[] = $lateness_time_dateFormat;
    //       dump(array_search($cont->vehicle_uid ,$latenessPerVehicleNumber));
    //       if (array_search($cont->vehicle_uid ,$latenessPerVehicleNumber) == $cont->vehicle_uid){
    //         dump(true);
    //       }
    //     }

    //   }        dump($latenessPerVehicleNumber);
    //     dump($latenessPerVehicle);
    // }
    /*
      FIN TEMPS DE RETARD MOYENS PAR VEHICULE
    */

    /*
      RECHERCHE CUSTOMER PAR NOM
    */

        // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
        $customerByNames = null;
        // Condition pour vérifier que la méthode HTTP est POST
        if ($request->isMethod('POST')) {
          // Récupère l'input utilisateur et le stocke dans une variable
            $customerFirstName_input = $request->request->get('FirstName_input');
            $customerLastName_input = $request->request->get('LastName_input');

          // Récupère la db grace au service et cherche l'objet concerné par l'id saisi ci-dessus
            $customerFirstName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['first_name' => $customerFirstName_input]);
            $customerLastName= $this->mongoDBService->getDatabase('Customer')->customers->findOne(['second_name' => $customerLastName_input]);
          // Si l'objet est trouvé
            if ($customerFirstName == $customerLastName && $customerFirstName != null) {
              // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
              $customerByNames = [$customerFirstName];
            }
        }
        
    /*
      FIN RECHERCHE CUSTOMER PAR NOM
    */

    /*
      RECHERCHE VEHICULE PAR IMMAT
    */

        // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
        $vehicleByPlate = null;
        // Condition pour vérifier que la méthode HTTP est POST
        if ($request->isMethod('POST')) {
          // Récupère l'input utilisateur et le stocke dans une variable
            $immat_input = $request->request->get('immat_input');

          // Récupère la db grace au service et cherche l'objet concerné par l'id saisi ci-dessus
          $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['licence_plate' => $immat_input]);
          // Si l'objet est trouvé
            if ($vehicle) {
              // Fait correspondre un objet de billing avec la clef primaire de l'objet trouvé
              $vehicleByPlate = [$vehicle];
            }
        }
        
    /*
      FIN RECHERCHE VEHICULE PAR IMMAT
    */

    /*
      RECHERCHE VEHICULE PAR KILOMETRAGE
    */

        // Init et permet de ne pas lancer de valeurs dans le twig si l'objet n'est pas trouvé
        $vehicleByKm = [];
        // Condition pour vérifier que la méthode HTTP est POST
        //if ($request->isMethod('POST')) {
          // Récupère l'input utilisateur et le stocke dans une variable
            $km_input = $request->request->get('km_input');
          // Récupère la db grace au service et cherche l'objet concerné par l'id saisi ci-dessus
            $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();

            foreach($vehicles as $car){
              if ($car && $car->km > $km_input){
                $vehicleByKm[] = $car;
              }
            }
            dump($vehicleByKm);
          // Si l'objet est trouvé
            
        //}
        
    /*
      FIN RECHERCHE VEHICULE PAR KILOMETRAGE
    */

    // Render des variables pour afficahge dans le twig
    return $this->render('search/index.html.twig', [
      'billingById' => $billingById, 
      'contractById' => $contractById,
      'unpayedContracts' => $unpayedContracts,
      'lateContracts' => $lateContracts,
      'currentContract' => $currentContract,
      'customerById' => $customerById, 
      'vehicleById' => $vehicleById, 
      'latenessPerCustomer' => $latenessPerCustomer,
      'latenessNumber' => $latenessNumber,
      'averageLatenessPerVehicle' => $averageLatenessPerVehicle,
      'indicesOfAverageLateness' => $indicesOfAverageLateness,
      'customerByNames' => $customerByNames,
      'vehicleByPlate' => $vehicleByPlate,
      'vehicleByKm' => $vehicleByKm,
    ]);
  }
}
