<?php

namespace App\Controller;

use App\Entity\Billing;
use App\Form\BillingType;
use App\Repository\BillingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/billing')]
class BillingController extends AbstractController
{
    #[Route('/', name: 'app_billing_index', methods: ['GET'])]
    public function index(BillingRepository $billingRepository): Response
    {
        return $this->render('billing/index.html.twig', [
            'billings' => $billingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_billing_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $billing = new Billing();
        $form = $this->createForm(BillingType::class, $billing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($billing);
            $entityManager->flush();

            return $this->redirectToRoute('app_billing_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('billing/new.html.twig', [
            'billing' => $billing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_billing_show', methods: ['GET'])]
    public function show(Billing $billing): Response
    {
        return $this->render('billing/show.html.twig', [
            'billing' => $billing,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_billing_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Billing $billing, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BillingType::class, $billing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_billing_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('billing/edit.html.twig', [
            'billing' => $billing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_billing_delete', methods: ['POST'])]
    public function delete(Request $request, Billing $billing, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$billing->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($billing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_billing_index', [], Response::HTTP_SEE_OTHER);
    }
}
