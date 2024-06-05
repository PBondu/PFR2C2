<?php

// // src/Controller/ProduitController.php

// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Doctrine\DBAL\Connection;

// class ProduitController extends AbstractController
// {
//     private $connection;

//     public function __construct(Connection $connection)
//     {
//         $this->connection = $connection;
//     }

//     public function index(): Response
//     {
//         $sql = "SELECT * FROM produit";
//         $stmt = $this->connection->prepare($sql);
//         $stmt->execute();
//         $produits = $stmt->fetchAll();

//         return $this->render('produit/index.html.twig', [
//             'produits' => $produits,
//         ]);
//     }
// }