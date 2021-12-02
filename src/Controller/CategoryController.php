<?php

namespace App\Controller;

use App\Entity\Program;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/category", name="category_")
 */
class CategoryController extends AbstractController
{
    /**
     * 
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(category::class)
            ->findAll();

        return $this->render(
            'category/index.html.twig',
            ['categories' => $categories,]
        );
    }



    /**
     * @Route("/{categoryName}/", methods={"GET"}, name="show")
     */
    public function show(string $categoryName): Response
    {


        $verifyCategory = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(
                ['name' => $categoryName],
            );


        if (empty($verifyCategory)) {

            throw $this->createNotFoundException(
                'Aucune catégorie appelée ' . $categoryName
            );
        }

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['Category' => $verifyCategory[0]->getId()],
                ['id' => 'DESC'],
                3,
                0
            );

        return $this->render('category/show.html.twig', [
            'programs' => $programs,
            'categoryName' => $categoryName,
        ]);
    }
}
