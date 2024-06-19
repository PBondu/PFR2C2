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
    dump($this->searchUnpayedContracts(true));
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
   * @return array Tableau des objets billing à afficher
   */
  public function searchBillingByContractId(?int $billingId)
  {
    if ($billingId !== null) {
      $billingByContractId = $this->billingRepository->findBy(['Contract_id' => $billingId]);
      if ($billingByContractId !== null) {
        return (array)$billingByContractId;
      } else {
        return [];
      }
    }
    return null;
  }

  /**
   * Recherche un contrat à partir de son ID
   * 
   * @param int ID du contrat rentré par l'utilisateur
   * @return array Tableau contenant le contrat à afficher
   */
  public function searchContractByContractId(?int $contractId)
  {
    if ($contractId !== null) {
      $contractByContractId = $this->contractRepository->findBy(['id' => $contractId]);
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
   * @return array|null Tableau contenant les contrats à afficher
   */
  public function searchContractByCustomerId(?int $customerId): array|null
  {
    if ($customerId !== null) {
      $contractByCustomerId = $this->contractRepository->findBy(['customer_uid' => $customerId]);
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
   * @return array|null Tableau contenant les contrats à afficher
   */
  public function searchContractByVehicleId(?int $vehicleId): array|null
  {
    if ($vehicleId !== null) {
      $contractByVehicleId = $this->contractRepository->findBy(['vehicle_uid' => $vehicleId]);
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
   * @return array Tableau contenant les contrats à afficher
   */
  public function searchUnpayedContracts(bool $showUnpayed)
  {
    $unpayedContracts = [];
    if ($showUnpayed) {
      foreach ($this->contractRepository->findAll() as $contract) {
        $billingFoundByContractId = $this->billingRepository->findBy(['Contract_id' => $contract]);
        if (empty($billingFoundByContractId)) {
          $unpayedContracts[] = $contract;
        }
      }
    }
    return (array)$unpayedContracts;
  }

  /**
   * Recherche les contrats en retards
   * 
   * @return array Tableau contenant les contrats à afficher
   */
  private function searchLateContracts(bool $showLate)
  {
    $lateContracts = [];
    if ($showLate) {
      foreach ($this->contractRepository->findAll() as $contract) {
        if ($contract->locend_datetime < $contract->returning_datetime) {
          $lateContracts[] = $contract;
        }
      }
    }
    return (array)$lateContracts;
  }

  /**
   * Recherche les contrats en cours
   * 
   * @return array Tableau contenant les contrats à afficher
   */
  private function searchCurrentContracts(bool $showCurrent)
  {
    $currentContract = [];
    if ($showCurrent) {
      foreach ($this->contractRepository->findAll() as $contract) {
        if ($contract->returning_datetime === null) {
          $currentContract[] = $contract;
        }
      }
    }
    return (array)$currentContract;
  }

  /**
   * Calcul le nombre moyen d'heures de retard par client
   * 
   * @return int|float Nombre moyen d'heures de retard 
   */
  private function calculateLatenessPerCustomer(bool $showLateAverage): int|float
  {
    $latenessPerCustomer = 0;
    $AllCustomerNumber =  count($this->mongoDBService->getDatabase('Customer')->customers->find()->toArray());
    if ($showLateAverage) {
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
   * @param int Dates choisies par l'utilisateur
   * @return int Heures de retard entre deux dates
   */
  private function calculateLatenessBetweenDates(?int $beginDateLate, ?int $endDateLate): int
  {
    $latenessBetweenDates = 0;

    foreach ($this->contractRepository->findAll() as $cont) {
      if (
        $cont->locend_datetime < $cont->returning_datetime
        && strtotime($beginDateLate) < $cont->returning_datetime->getTimestamp()
        && strtotime($endDateLate) > $cont->returning_datetime->getTimestamp()
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
  private function calculateAverageLatenessPerVehicle(bool $showTimeLateAverage)
  {
    $averageLatenessPerVehicle = [];
    if ($showTimeLateAverage) {
      $vehicleLateness = [];
      foreach ($this->contractRepository->findAll() as $cont) {
        if ($cont->locend_datetime < $cont->returning_datetime) {
          $latenessTime = $cont->returning_datetime->getTimestamp() - $cont->locend_datetime->getTimestamp();

          if (!isset($vehicleLateness[$cont->vehicle_uid])) {
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
    return (array)$averageLatenessPerVehicle;
  }

  /**
   * Recherche d'un client à partir de son nom et prénom
   * 
   * @param string Les nom et prénom du client entrés par l'utilisateur
   * @return array|null Tableau contenant le client
   */
  private function searchCustomerByName(?string $firstName, ?string $lastName): array|null
  {
    $customerByNames = null;
    $customerFoundByFirstName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['first_name' => $firstName]);
    $customerFoundByLastName = $this->mongoDBService->getDatabase('Customer')->customers->findOne(['second_name' => $lastName]);
    if ($customerFoundByFirstName == $customerFoundByLastName && $customerFoundByFirstName !== null) {
      $customerByNames = [$customerFoundByFirstName];
    }
    return (array)$customerByNames;
  }

  /**
   * Recherche d'un véhicule à partir de son immatriculation
   * 
   * @param string l'immatriculation du véhicule entré par l'utilisateur
   * @return array|null Tableau contenant le véhicule | ou null si pas d'entrée
   */
  private function searchVehicleByPlate(?string $licencePlate): array|null
  {
    $vehicleByPlate = null;

    $vehicle = $this->mongoDBService->getDatabase('Vehicle')->vehicles->findOne(['licence_plate' => $licencePlate]);
    if ($vehicle) {
      $vehicleByPlate = [$vehicle];
    }
    return (array)$vehicleByPlate;
  }

  /**
   * Recherche des véhicules supérieur à un kilométrage donné
   * 
   * @param string Kilométrage entré par l'utilisateur
   * @return array Tableau contenant les véhicule ayant un kilométrage supérieur à celui rentré par l'utilisateur
   */
  private function searchVehicleByKm(?string $kmInput): array
  {
    $vehicleByKm = [];
    $vehicles = $this->mongoDBService->getDatabase('Vehicle')->vehicles->find()->toArray();
    foreach ($vehicles as $car) {
      if ($kmInput && $car->km > $kmInput) {
        $vehicleByKm[] = $car;
      }
    }
    return (array)$vehicleByKm;
  }
}
