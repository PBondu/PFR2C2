<?php

namespace App\Controller;

use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/search')]
class SearchController extends AbstractController
{
  private $mongoDBService;
  private $contractRepository;
  private $billingRepository;

  public function __construct(MongoDBService $mongoDBService, ContractRepository $contractRepository, BillingRepository $billingRepository)
  {
    $this->mongoDBService = $mongoDBService;
    $this->contractRepository = $contractRepository;
    $this->billingRepository = $billingRepository;
  }

  #[Route('/', name: 'app_search_index', methods: ['GET', 'POST'])]
  public function index(Request $request): Response
  {
    $billingByContractId = $this->searchBillingByContractId($request);
    $contractByContractId = $this->searchContractByContractId($request);
    $contractByCustomerId = $this->searchContractByCustomerId($request);
    $contractByVehicleId = $this->searchContractByVehicleId($request);
    $unpayedContracts = $this->searchUnpayedContracts($request);
    $lateContracts = $this->searchLateContracts($request);
    $currentContract = $this->searchCurrentContracts($request);
    $latenessPerCustomer = $this->calculateLatenessPerCustomer($request);
    $latenessBetweenDates = $this->calculateLatenessBetweenDates($request);
    $averageLatenessPerVehicle = $this->calculateAverageLatenessPerVehicle($request);
    $indicesOfAverageLateness = array_keys($averageLatenessPerVehicle);
    $customerByNames = $this->searchCustomerByName($request);
    $vehicleByPlate = $this->searchVehicleByPlate($request);
    $vehicleByKm = $this->searchVehicleByKm($request);

    return $this->render('search/index.html.twig', [
      'billingByContractId' => $billingByContractId,
      'contractByContractId' => $contractByContractId,
      'unpayedContracts' => $unpayedContracts,
      'lateContracts' => $lateContracts,
      'currentContract' => $currentContract,
      'contractByCustomerId' => $contractByCustomerId,
      'contractByVehicleId' => $contractByVehicleId,
      'latenessPerCustomer' => $latenessPerCustomer,
      'latenessBetweenDates' => $latenessBetweenDates,
      'averageLatenessPerVehicle' => $averageLatenessPerVehicle,
      'indicesOfAverageLateness' => $indicesOfAverageLateness,
      'customerByNames' => $customerByNames,
      'vehicleByPlate' => $vehicleByPlate,
      'vehicleByKm' => $vehicleByKm,
    ]);
  }

  /**
   * Recherche une ou plusieurs factures à partir d'un ID contrat
   * 
   * @param input ID du contrat rentré par l'utilisateur
   * @return array Tableau des objets billing à afficher
   */
  private function searchBillingByContractId(Request $request)
  {
    $billingByContractId = null;
    $userInput = $request->request->get('billing_id');
    $contractFound = $this->contractRepository->findBy(['id' => (int)$userInput]);
    if ($contractFound) {
      $billingByContractId = $this->billingRepository->findBy(['Contract_id' => $contractFound]);
    }
    return $billingByContractId;
  }

  /**
   * Recherche un contrat à partir de son ID
   * 
   * @param input ID du contrat rentré par l'utilisateur
   * @return array Tableau contenant le contrat à afficher
   */
  private function searchContractByContractId(Request $request)
  {
    $contractByContractId = null;
    $userInput = $request->request->get('contract_id');
    $contractByContractId = $this->contractRepository->findBy(['id' => (int)$userInput]);
    if ($contractByContractId) {
      return $contractByContractId;
    }
  }

  /**
   * Recherche un ou plusieurs contrats à partir de l'ID du client
   * 
   * @param input ID du client rentré par l'utilisateur
   * @return array|null Tableau contenant les contrats à afficher
   */
  private function searchContractByCustomerId(Request $request): array|null
  {
    $contractByCustomerId = null;
    $userInput = $request->request->get('customer_id');
    $customerFound = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['_id' => (int)$userInput]);
    if ($customerFound) {
      $idCustomerFound = $customerFound['_id'];
      $contractByCustomerId = $this->contractRepository->findBy(['customer_uid' => $idCustomerFound]);
    }
    return $contractByCustomerId;
  }

  /**
   * Recherche un ou plusieurs contrats à partir de l'ID d'un véhicule
   * 
   * @param input ID du véhicule rentré par l'utilisateur
   * @return array|null Tableau contenant les contrats à afficher
   */
  private function searchContractByVehicleId(Request $request): array|null
  {
    $contractByVehicleId = null;
    $userInput = $request->request->get('vehicle_id');
    $vehicleFound = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['_id' => (int)$userInput]);
    if ($vehicleFound) {
      $idVehicleFound = $vehicleFound['_id'];
      $contractByVehicleId = $this->contractRepository->findBy(['vehicle_uid' => $idVehicleFound]);
    }
    return $contractByVehicleId;
  }

  /**
   * Recherche les contrats impayés | les contrats sans factures associées
   * 
   * @return array Tableau contenant les contrats à afficher
   */
  private function searchUnpayedContracts(Request $request)
  {
    $unpayedContracts = [];
    if ($request->request->get('unpayed') == 'show') {
      foreach ($this->contractRepository->findAll() as $cont) {
        $billingFoundByContractId = $this->billingRepository->findBy(['Contract_id' => $cont]);
        if (empty($billingFoundByContractId)) {
          $unpayedContracts[] = $cont;
        }
      }
    }
    return $unpayedContracts;
  }

  /**
   * Recherche les contrats en retards
   * 
   * @return array Tableau contenant les contrats à afficher
   */
  private function searchLateContracts(Request $request)
  {
    $lateContracts = [];
    if ($request->request->get('late') == 'show') {
      foreach ($this->contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
          $lateContracts[] = $cont;
        }
      }
    }
    return $lateContracts;
  }

  /**
   * Recherche les contrats en cours
   * 
   * @return array Tableau contenant les contrats à afficher
   */
  private function searchCurrentContracts(Request $request)
  {
    $currentContract = [];
    if ($request->request->get('current') == 'show') {
      foreach ($this->contractRepository->findAll() as $cont) {
        if ($cont->returning_datetime === null) {
          $currentContract[] = $cont;
        }
      }
    }
    return $currentContract;
  }

  /**
   * Calcul le nombre moyen d'heures de retard par client
   * 
   * @return int|float Nombre moyen d'heures de retard 
   */
  private function calculateLatenessPerCustomer(Request $request): int|float
  {
    $latenessPerCustomer = 0;
    $AllCustomerNumber =  count($this->mongoDBService->getDatabase('Customer')->customers->find()->toArray());
    if ($request->request->get('lateAverage') == 'show') {
      foreach ($this->contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
          ++$latenessPerCustomer;
        }
      }
      $latenessPerCustomer = $latenessPerCustomer / $AllCustomerNumber;
    }
    return $latenessPerCustomer;
  }

  /**
   * Calcul le nombre de retard entre deux dates
   * 
   * @param input Dates choisies par l'utilisateur
   * @return int Heures de retard entre deux dates
   */
  private function calculateLatenessBetweenDates(Request $request): int
  {
    $latenessBetweenDates = 0;
    $userInputBeginDate = $request->request->get('beginDateLate');
    $userInputEndDate = $request->request->get('endDateLate');

    foreach ($this->contractRepository->findAll() as $cont) {
      if (
        $cont->locend_datetime < $cont->returning_datetime
        && strtotime($userInputBeginDate) < $cont->returning_datetime->getTimestamp()
        && strtotime($userInputEndDate) > $cont->returning_datetime->getTimestamp()
      ) {
        ++$latenessBetweenDates;
      }
    }
    return $latenessBetweenDates;
  }

  /**
   * Calcul du nombre d'heures de retard moyen pour chaque véhicules
   * 
   * @return array Tableau contenant les heures de retard pour chaque véhicules
   */
  private function calculateAverageLatenessPerVehicle(Request $request)
  {
    $averageLatenessPerVehicle = [];
    if ($request->request->get('timeLateAverage') == 'show') {
      $vehicleLateness = [];
      foreach ($this->contractRepository->findAll() as $cont) 
      {
        if ($cont->locend_datetime < $cont->returning_datetime) 
        {
          $latenessTime = $cont->returning_datetime->getTimestamp() - $cont->locend_datetime->getTimestamp();

          if (!isset($vehicleLateness[$cont->vehicle_uid])) 
          {
            $vehicleLateness[$cont->vehicle_uid] = [];
          }
          $vehicleLateness[$cont->vehicle_uid][] = $latenessTime;
        }
      }
      foreach ($vehicleLateness as $vehicleUid => $latenessTimes) {
        $totalLateness = array_sum($latenessTimes);
        $averageLateness = $totalLateness / count($latenessTimes);
        $days = floor($averageLateness / (60 * 60 * 24));
        $hours = floor(($averageLateness % (60 * 60 * 24)) / (60 * 60));
        $averageLatenessPerVehicle[$vehicleUid] = $days . ' jours et ' . $hours . ' heures';
      }
    }
    return $averageLatenessPerVehicle;
  }

  /**
   * Recherche d'un client à partir de son nom et prénom
   * 
   * @param input Les nom et prénom du client entrés par l'utilisateur
   * @return array|null Tableau contenant le client
   */
  private function searchCustomerByName(Request $request): array|null
  {
    $customerByNames = null;
      $firstNameInput = $request->request->get('FirstName_input');
      $lastNameInput = $request->request->get('LastName_input');
      $customerFoundByFirstName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['first_name' => $firstNameInput]);
      $customerFoundByLastName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['second_name' => $lastNameInput]);
      if ($customerFoundByFirstName == $customerFoundByLastName && $customerFoundByFirstName !== null) {
        $customerByNames = [$customerFoundByFirstName];
      }
    return $customerByNames;
  }

  /**
   * Recherche d'un véhicule à partir de son immatriculation
   * 
   * @param input l'immatriculation du véhicule entré par l'utilisateur
   * @return array|null Tableau contenant le véhicule | ou null si pas d'entrée
   */
  private function searchVehicleByPlate(Request $request): array|null
  {
    $vehicleByPlate = null;
      $userInput = $request->request->get('immat_input');
      $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['licence_plate' => $userInput]);
      if ($vehicle) {
        $vehicleByPlate = [$vehicle];
      }
    return $vehicleByPlate;
  }

  /**
   * Recherche des véhicules supérieur à un kilométrage donné
   * 
   * @param input Kilométrage entré par l'utilisateur
   * @return array Tableau contenant les véhicule ayant un kilométrage supérieur à celui rentré par l'utilisateur
   */
  private function searchVehicleByKm(Request $request): array
  {
    $vehicleByKm = [];
    if ($request->isMethod('POST')) {
      $userInput = null;
      $userInput = $request->request->get('km_input');
      $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();
      foreach ($vehicles as $car) {
        if ($userInput && $car->km > $userInput) {
          $vehicleByKm[] = $car;
        }
      }
    }
    return $vehicleByKm;
  }
}
