<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class ContractByCustomerIdTest extends TestCase
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

    public function testContractFoundByCustomerId(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $customer_uid = 1;
        $contractFound = [(object)['customer_uid' => $customer_uid]];

        $this->contractRepository->method('findBy')
            ->with(['customer_uid' => $customer_uid])
            ->willReturn($contractFound);

        $functionResult = $searchClass->searchContractByCustomerId($customer_uid);

        $this->assertNotEmpty($functionResult, 'Le tableau est vide.');
        $this->assertSame($contractFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }

    public function testContractNotFoundByCustomerId(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $customer_uid = 9999999999;
        $contractNotFound = [];

        $this->contractRepository->method('findBy')
            ->with(['customer_uid' => $customer_uid])
            ->willReturn($contractNotFound);

        $functionResult = $searchClass->searchContractByCustomerId($customer_uid);

        $this->assertEmpty($functionResult, 'Le tableau n\'est pas vide.');
        $this->assertSame($contractNotFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }
}