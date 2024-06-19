<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class UnpayedContractTest extends TestCase
{
  private $contractRepository;
  private $billingRepository;
  private $mongoDBService;
  private $userRequestProvider;

  protected function setUp(): void
  {
    $this->contractRepository = $this->createMock(ContractRepository::class);
    $this->billingRepository = $this->createMock(BillingRepository::class);
    $this->mongoDBService = $this->createMock(MongoDBService::class);
    $this->userRequestProvider = $this->createMock(UserRequestProvider::class);
  }

  public function testUnpayedContractFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $contracts = [
      (object)['id' => 1]
    ];

    $billFound = [];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $this->billingRepository->method('findBy')
      ->willReturn($billFound);

    $functionResult = $searchClass->searchUnpayedContracts(true);

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertCount(1, $functionResult, 'La configurations des variables d\'entrée sont incorrectes');
  }

  public function testUnpayedContractNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $contracts = [
      (object)['id' => 1]
    ];

    $billFound = ['id' => 1];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $this->billingRepository->method('findBy')
      ->willReturn($billFound);

    $functionResult = $searchClass->searchUnpayedContracts(true);

    $this->assertEmpty($functionResult, 'Le tableau est vide');
  }
}
