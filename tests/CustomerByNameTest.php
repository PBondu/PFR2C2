<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;
use MongoDB\Collection;

class CustomerByNameTest extends TestCase
{
  private $contractRepository;
  private $billingRepository;
  private $mongoDBService;
  private $userRequestProvider;
  private $customersCollection;

  protected function setUp(): void
  {
    $this->contractRepository = $this->createMock(ContractRepository::class);
    $this->billingRepository = $this->createMock(BillingRepository::class);
    $this->mongoDBService = $this->createMock(MongoDBService::class);
    $this->userRequestProvider = $this->createMock(UserRequestProvider::class);
    $this->customersCollection = $this->createMock(Collection::class);
  }

  public function testCustomerByNameFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $customers = [
      (object)[
        'first_name' => "Paul",
        'second_name' => "Icier"
      ],
    ];

    $this->mongoDBService->method('getDatabase')
    ->with('Customer')
    ->willReturn((object)['customers' => new class($customers) {
        public $customers;
        public function __construct($customers) {
            $this->customers = $customers;
        }
        public function findOne() {
            return new class($this->customers) {
                public $customers;
                public function __construct($customers) {
                    $this->customers = $customers;
                }
            };
        }
    }]);

    $functionResult = $searchClass->searchCustomerByName("Paul", "Icier");

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertEquals("Paul", $functionResult[0]->{'customers'}[0]->first_name, 'La configuration de du service est incorrecte');
  }

  public function testCustomerByNameNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $this->mongoDBService->method('getDatabase')
    ->with('Customer')
    ->willReturn((object)['customers' => $this->customersCollection]);

// Mock de findOne pour retourner null en fonction du critère
$this->customersCollection->method('findOne')
    ->will($this->returnValueMap([
        [['first_name' => 'Sarah'], null, null],
        [['second_name' => 'letoutletemps'], null, null],
    ]));

    $functionResult = $searchClass->searchCustomerByName("Sarah", "letoutletemps");


    $this->assertEmpty($functionResult, 'Le tableau n\'est pas vide');  
   }
}
