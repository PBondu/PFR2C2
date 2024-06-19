<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class BillingByContractId extends TestCase
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

    public function testBillingFoundByContractId(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $billingId = 3;
        $billFound = [(object)['id' => $billingId]];
        //$billFound = [(object)['id' => 1 ]];

        $this->billingRepository->method('findBy')
            ->with(['Contract_id' => $billingId])
            ->willReturn($billFound);

        $functionResult = $searchClass->searchBillingByContractId($billingId);

        $this->assertNotEmpty($functionResult, 'Le tableau est vide.');
        $this->assertSame($billFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }

    public function testBillingNotFoundByContractId(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $billingId = 9999999999;
        $billingNotFound = [];

        $this->billingRepository->method('findBy')
            ->with(['Contract_id' => $billingId])
            ->willReturn($billingNotFound);

        $functionResult = $searchClass->searchBillingByContractId($billingId);

        $this->assertEmpty($functionResult, 'Le tableau n\'est pas vide.');
        $this->assertSame($billingNotFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }
}