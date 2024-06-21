<?php

namespace App\Controller;

use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
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
  private $UserRequestProvider;

  public function __construct(
    MongoDBService $mongoDBService,
    ContractRepository $contractRepository,
    BillingRepository $billingRepository,
    UserRequestProvider $UserRequestProvider
  ) {
    $this->mongoDBService = $mongoDBService;
    $this->contractRepository = $contractRepository;
    $this->billingRepository = $billingRepository;
    $this->UserRequestProvider = $UserRequestProvider;
  }

  #[Route('/', name: 'app_search_index', methods: ['GET', 'POST'])]
  public function index(Request $request): Response
  {
    // Déclaration des variables stockant les inputs utilisateur
    $billingId = $this->UserRequestProvider->getBillingId($request);
    $contractId = $this->UserRequestProvider->getContractId($request);
    $customerId = $this->UserRequestProvider->getCustomerId($request);
    $vehicleId = $this->UserRequestProvider->getVehicleId($request);
    $showUnpayed = $this->UserRequestProvider->getShowParameter($request, 'unpayed');
    $showLate = $this->UserRequestProvider->getShowParameter($request, 'late');
    $showCurrent = $this->UserRequestProvider->getShowParameter($request, 'current');
    $showLateAverage = $this->UserRequestProvider->getShowParameter($request, 'lateAverage');
    $beginDateLate = $this->UserRequestProvider->getBeginDateLate($request);
    $endDateLate = $this->UserRequestProvider->getEndDateLate($request);
    $showTimeLateAverage = $this->UserRequestProvider->getShowParameter($request, 'timeLateAverage');
    $firstName = $this->UserRequestProvider->getFirstName($request);
    $lastName = $this->UserRequestProvider->getLastName($request);
    $licencePlate = $this->UserRequestProvider->getLicencePlate($request);
    $kmInput = $this->UserRequestProvider->getKmInput($request);

    // Lancement des fonctions avec l'input utilisateur en paramètre
    $billingByContractId = $this->searchBillingByContractId($billingId);
    $contractByContractId = $this->searchContractByContractId($contractId);
    $contractByCustomerId = $this->searchContractByCustomerId($customerId);
    $contractByVehicleId = $this->searchContractByVehicleId($vehicleId);
    $unpayedContracts = $this->searchUnpayedContracts($showUnpayed);
    $lateContracts = $this->searchLateContracts($showLate);
    $currentContract = $this->searchCurrentContracts($showCurrent);
    $latenessPerCustomer = $this->calculateLatenessPerCustomer($showLateAverage);
    $latenessBetweenDates = $this->calculateLatenessBetweenDates($beginDateLate, $endDateLate);
    $averageLatenessPerVehicle = $this->calculateAverageLatenessPerVehicle($showTimeLateAverage);
    $indicesOfAverageLateness = array_keys($averageLatenessPerVehicle);
    $customerByNames = $this->searchCustomerByName($firstName, $lastName);
    $vehicleByPlate = $this->searchVehicleByPlate($licencePlate);
    $vehicleByKm = $this->searchVehicleByKm($kmInput);

    // Valeurs retournées dans le template twig
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
   * @param int ID du contrat rentré par l'utilisateur
   * @return array|[]|null Tableau des objets billing à afficher
   */
  public function searchBillingByContractId(?int $billingId): ?array
  {
    if ($billingId !== null) {
      $billingByContractId = $this->billingRepository->findBy(['Contract_id' => $billingId]); // Cherche une facture à partir de l'input user
      if ($billingByContractId !== null) { // Si trouvé
        return (array)$billingByContractId; // Retourne la facture trouvée
      } else {
        return []; // Permet d'afficher pas de factures trouvées dans le twig
      }
    }
    return null; // Permet d'afficher pas de factures trouvées dans le twig
  }

  /**
   * Recherche un contrat à partir de son ID
   * 
   * @param int ID du contrat rentré par l'utilisateur
   * @return array|[]|null Tableau contenant le contrat à afficher
   */
  public function searchContractByContractId(?int $contractId): ?array
  {
    if ($contractId !== null) {
      $contractByContractId = $this->contractRepository->findBy(['id' => $contractId]); // Cherche un contrat à partir de l'input user
      if ($contractByContractId !== null) {
        return (array)$contractByContractId;
      } else {
        return [];
      }
    }
    return null;
  }

  /**
   * Recherche un ou plusieurs contrats à partir de l'ID du client
   * 
   * @param int ID du client rentré par l'utilisateur
   * @return array|[]|null Tableau contenant les contrats à afficher
   */
  public function searchContractByCustomerId(?int $customerId): ?array
  {
    if ($customerId !== null) {
      $contractByCustomerId = $this->contractRepository->findBy(['customer_uid' => $customerId]); // Cherche un contrat à partir de l'input user
      if ($contractByCustomerId !== null) {
        return (array)$contractByCustomerId;
      } else {
        return [];
      }
    }
    return null;
  }

  /**
   * Recherche un ou plusieurs contrats à partir de l'ID d'un véhicule
   * 
   * @param int ID du véhicule rentré par l'utilisateur
   * @return array|[]|null Tableau contenant les contrats à afficher
   */
  public function searchContractByVehicleId(?int $vehicleId): ?array
  {
    if ($vehicleId !== null) {
      $contractByVehicleId = $this->contractRepository->findBy(['vehicle_uid' => $vehicleId]); // Cherche un contrat à partir de l'input user
      if ($contractByVehicleId !== null) {
        return (array)$contractByVehicleId;
      } else {
        return [];
      }
    }
    return null;
  }

  /**
   * Recherche les contrats impayés | les contrats sans factures associées
   * 
   * @param bool Bouton "show" cliqué par l'utilisateur renvoitTRUE
   * @return array|[] Tableau contenant les contrats à afficher
   */
  public function searchUnpayedContracts(bool $showUnpayed): ?array
  {
    $unpayedContracts = [];
    if ($showUnpayed) {
      foreach ($this->contractRepository->findAll() as $contract) { // Pour chaque contrat
        $billingFoundByContractId = $this->billingRepository->findBy(['Contract_id' => $contract]); // Cherche une facture
        if (empty($billingFoundByContractId)) { // Si contrat sans facture
          $unpayedContracts[] = $contract;  // Ajout du contrat au tableau
        }
      }
      return !empty($unpayedContracts) ? (array)$unpayedContracts : [];
    }
    else{
      return null;
    }
  }

  /**
   * Recherche les contrats en retards
   * 
   * @param bool Bouton "show" cliqué par l'utilisateur renvoitTRUE
   * @return array|[] Tableau contenant les contrats à afficher
   */
  public function searchLateContracts(bool $showLate): ?array
  {
    $lateContracts = [];
    if ($showLate) {
      foreach ($this->contractRepository->findAll() as $contract) { // Pour chaque contrat
        if ($contract->locend_datetime < $contract->returning_datetime) { // Si le véhicule est retourné après la date de fin du contrat
          $lateContracts[] = $contract; // Ajout du contrat au tableau
        }
      }      
      return !empty($lateContracts) ? (array)$lateContracts : [];
    }
    else{
      return null;
    }
  }

  /**
   * Recherche les contrats en cours
   * 
   * @param bool Bouton "show" cliqué par l'utilisateur renvoitTRUE
   * @return array|[]|null Tableau contenant les contrats à afficher
   */
  public function searchCurrentContracts(bool $showCurrent): ?array
  {
    $currentContract = [];
    if ($showCurrent) {
      foreach ($this->contractRepository->findAll() as $contract) { // Pour chaque contrat
        if ($contract->returning_datetime === null) { // Si le véhicule n'est pas encore retourné
          $currentContract[] = $contract; // Ajout du contrat au tableau
        }
      }
      return !empty($currentContract) ? (array)$currentContract : [];
    }
    else{
      return null;
    }
  }

  /**
   * Calcul le nombre moyen d'heures de retard par client
   * 
   * @param bool Bouton "show" cliqué par l'utilisateur renvoitTRUE
   * @return int|float Nombre moyen d'heures de retard 
   */
  public function calculateLatenessPerCustomer(bool $showLateAverage): int|float
  {
    $latenessPerCustomer = 0;
    // compte le nombre de clients dans la db
    $AllCustomerNumber =  count($this->mongoDBService->getDatabase('Customer')->customers->find()->toArray());
    if ($showLateAverage) {
      foreach ($this->contractRepository->findAll() as $cont) { // Pour chaque contrat
        if ($cont->locend_datetime < $cont->returning_datetime) { // Si le véhicule est retourné après la date de fin du contrat
          ++$latenessPerCustomer; // Incrément du nombre de retard
        }
      }
      $latenessPerCustomer = $latenessPerCustomer / $AllCustomerNumber; // Moyenne du nombre de retard par client
    }
    return $latenessPerCustomer;
  }

  /**
   * Calcul le nombre de retard entre deux dates
   * 
   * @param ?string Dates choisies par l'utilisateur
   * @return int Heures de retard entre deux dates
   */
  public function calculateLatenessBetweenDates(?string $beginDateLate, ?string $endDateLate): int
  {
    $latenessBetweenDates = 0;

    foreach ($this->contractRepository->findAll() as $cont) { // Pour chaque contrat
      if (
        // Si les dates des retards sont comprises entre les dates entrées par le user
        $cont->locend_datetime < $cont->returning_datetime
        && strtotime($beginDateLate) < $cont->returning_datetime->getTimestamp()
        && strtotime($endDateLate) > $cont->returning_datetime->getTimestamp()
      ) {
        ++$latenessBetweenDates; // Incrément du nombre de retard
      }
    }
    return $latenessBetweenDates;
  }

  /**
   * Calcul du nombre d'heures de retard moyen pour chaque véhicules
   * 
   * @param bool Bouton "show" cliqué par l'utilisateur renvoitTRUE
   * @return array|null Tableau contenant les heures de retard pour chaque véhicules
   */
public function calculateAverageLatenessPerVehicle(bool $showTimeLateAverage): ?array
{
    $averageLatenessPerVehicle = []; 
    if ($showTimeLateAverage) {
        $vehicleLateness = [];
        foreach ($this->contractRepository->findAll() as $cont) { // Pour chaque contrat
            if ($cont->locend_datetime < $cont->returning_datetime) { // Si le véhicule est retourné après la date de fin du contrat
                // Calcul du temps de retard
                $latenessTime = $cont->returning_datetime->getTimestamp() - $cont->locend_datetime->getTimestamp(); 
                $vehicleLateness[$cont->vehicle_uid][] = $latenessTime; // Ajout du temps de retard par véhicule au tableau associatif
            }
        }
        /*
          Permet d'additionner les temps de retard pour les mêmes véhicules
        */
        foreach ($vehicleLateness as $vehicleUid => $latenessTimes) { // Pour chaque retard dans le précédent tableau
            $averageLateness = array_sum($latenessTimes) / count($latenessTimes); // Additionne les temps de retards
            $days = floor($averageLateness / (60 * 60 * 24));  // Permet la conversion en jours
            $hours = floor(($averageLateness % (60 * 60 * 24)) / (60 * 60)); // Permet la conversion en heures
            $averageLatenessPerVehicle[$vehicleUid] = $days . ' jours et ' . $hours . ' heures'; // Affichage sous forme de jours et d'heures
        }
    }
    return (array)$averageLatenessPerVehicle;
}

  /**
   * Recherche d'un client à partir de son nom et prénom
   * 
   * @param string|null Les nom et prénom du client entrés par l'utilisateur
   * @return array|null Tableau contenant le client
   */
  public function searchCustomerByName(?string $firstName, ?string $lastName): ?array
  {
    if ($firstName !== null && $lastName !== null) {
      $customerByNames = null;
      // Cherche nom et prénom dans la db en fonction de l'input user
      $customerFoundByFirstName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['first_name' => $firstName]);
      $customerFoundByLastName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['second_name' => $lastName]);
      if ($customerFoundByFirstName == $customerFoundByLastName && $customerFoundByFirstName !== null) { // Si trouvé
        $customerByNames = [$customerFoundByFirstName]; // Ajout au tableau
      }
      return (array)$customerByNames;
    }
    return null;
  }

  /**
   * Recherche d'un véhicule à partir de son immatriculation
   * 
   * @param string|null l'immatriculation du véhicule entré par l'utilisateur
   * @return array|null Tableau contenant le véhicule | ou null si pas d'entrée
   */
  public function searchVehicleByPlate(?string $licencePlate): ?array
  {
    if ($licencePlate !== null) {
      $vehicleByPlate = null;
      // Cherche un véhicule dans la db en fonction de l'input user
      $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['licence_plate' => $licencePlate]);
      if ($vehicle) { // Si trouvé
        $vehicleByPlate = [$vehicle]; // Ajout au tableau
      }
      return (array)$vehicleByPlate;
    }
    return null;
  }

  /**
   * Recherche des véhicules supérieur à un kilométrage donné
   * 
   * @param string|null Kilométrage entré par l'utilisateur
   * @return array|null Tableau contenant les véhicule ayant un kilométrage supérieur à celui rentré par l'utilisateur
   */
  public function searchVehicleByKm(?string $kmInput): ?array
  {
    if ($kmInput !== null) {
      $vehicleByKm = [];
       // Cherche tous les véhicules dans la db 
      $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();
      foreach ($vehicles as $car) { // Pour chaque véhicules
        if ($kmInput && $car->km > $kmInput) { // Si le véhicule a un km supérieur à celui de l'input user
          $vehicleByKm[] = $car; // Ajout au tableau
        }
      }
      return (array)$vehicleByKm;
    }
    return null;
  }
}
