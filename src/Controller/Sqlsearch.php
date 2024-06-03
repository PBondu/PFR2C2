<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\Billing;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Sqlsearch extends AbstractController
{

  public function search(Request $request, Contract $contract): Response
      {
        $var = 1;

        return $this->render('contract/index.html.twig', [
            'contracts' => $contract
        ]);
    }

}