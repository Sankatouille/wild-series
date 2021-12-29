<?php

namespace App\Controller;


use App\Entity\Season;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Category;
use App\Service\Slugify;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Form\CategoryType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/program", name="program_")
 */
class ProgramController extends AbstractController
{



    /**
     * Show all rows from Program's entity
     *
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render(
            'program/index.html.twig',
            ['programs' => $programs,]
        );
    }

    /**
     * The controller for the category add form
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        // Create a new Program Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handleRequest($request);

        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {

            // Deal with the submitted data

            $program->setOwner($this->getUser());
            // Get the Entity Manager

            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($program->getTitle());

            $program->setSlug($slug);
            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);
            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', ["form" => $form->createView()]);
    }


    /**
     * Getting a program by id
     *
     *  @Route("/{slug}", methods={"GET"}, requirements={"id"="\d+"}, name="show")
     *
     * @return Response
     */
    public function show(Program $program): Response
    {

        // $seasons = $this->getDoctrine()
        //     ->getRepository(Season::class)
        //     ->findBy(['program' => $program->getId()]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $program . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $program->getSeasons(),
        ]);
    }


    /**
     * @Route("/{slug}/edit", name="program_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
         // Check wether the logged in user is the owner of the program
         if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

             return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }




    /**
     * Getting a program by id
     *
     *  @Route("/{id}/season/{season_id}", methods={"GET"}, requirements={"id"="\d+"}, name="showSeason")
     *
     * @return Response
     */
    public function showSeason(Program $program, Season $season_id): Response
    {
        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $season_id->getId()]);

        if (!$season_id) {
            throw $this->createNotFoundException(
                'No season with id : ' . $season_id . ' found in season\'s table.'
            );
        }
        return $this->render('program/showSeason.html.twig', [
            'season' => $season_id,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }


    /**
     * Getting a program by id
     *
     *  @Route("/{id}/season/{season_id}/episode/{episode_id}", methods={"GET", "POST"}, requirements={"id"="\d+"}, name="showEpisode")
     *
     * @return Response
     */
    public function showEpisode(Program $program, Season $season_id, Episode $episode_id, EntityManagerInterface $em, Request $request): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $comment->setAuthor($this->getUser());
            $comment->setEpisode($episode_id);

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('episode_show', ["slug" => $episode_id->getSlug()]);

        }


        if (!$episode_id) {
            throw $this->createNotFoundException(
                'No episode with id : ' . $episode_id . ' found in episode\'s table.'
            );
        }
        return $this->render('episode/show.html.twig', [
            'season' => $season_id,
            'program' => $program,
            'episode' => $episode_id,
            'form' => $form->createView()
        ]);
    }
}
