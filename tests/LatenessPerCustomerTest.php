<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class LatenessPerCustomerTest extends TestCase
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

  public function testCalculateLatenessPerCustomerWithShowLateAverageTrue(): void
  {
      $searchClass = new SearchController(
          $this->mongoDBService,
          $this->contractRepository,
          $this->billingRepository,
          $this->userRequestProvider
      );

      $contracts = [
          (object)['locend_datetime' => new \DateTime('2022-01-01'), 'returning_datetime' => new \DateTime('2022-01-02')],
          (object)['locend_datetime' => new \DateTime('2022-01-02'), 'returning_datetime' => new \DateTime('2022-01-01')],
          (object)['locend_datetime' => new \DateTime('2022-01-03'), 'returning_datetime' => new \DateTime('2022-01-04')]
      ];

      $customers = [
          (object)[],
          (object)[],
          (object)[]
      ];

      $this->contractRepository->method('findAll')
          ->willReturn($contracts);

      $this->mongoDBService->method('getDatabase')
          ->with('Customer')
          ->willReturn((object)['customers' => new class($customers) {
              private $customers;
              public function __construct($customers) {
                  $this->customers = $customers;
              }
              public function find() {
                  return new class($this->customers) {
                      private $customers;
                      public function __construct($customers) {
                          $this->customers = $customers;
                      }
                      public function toArray() {
                          return $this->customers;
                      }
                  };
              }
          }]);

      $result = $searchClass->calculateLatenessPerCustomer(true);

      $this->assertEquals(2 / 3, $result, 'Le calcul de lateness per customer est incorrect');
  }

  public function testCalculateLatenessPerCustomerWithShowLateAverageFalse(): void
  {
      $searchClass = new SearchController(
          $this->mongoDBService,
          $this->contractRepository,
          $this->billingRepository,
          $this->userRequestProvider
      );

    $customers = [
        (object)[],
        (object)[],
        (object)[]
    ];

    $this->mongoDBService->method('getDatabase')
        ->with('Customer')
        ->willReturn((object)['customers' => new class($customers) {
            private $customers;
            public function __construct($customers) {
                $this->customers = $customers;
            }
            public function find() {
                return new class($this->customers) {
                    private $customers;
                    public function __construct($customers) {
                        $this->customers = $customers;
                    }
                    public function toArray() {
                        return $this->customers;
                    }
                };
            }
        }]);

      $result = $searchClass->calculateLatenessPerCustomer(false);

      $this->assertEquals(0, $result, 'Le résultat doit être 0 lorsque showLateAverage est false');
  }
}