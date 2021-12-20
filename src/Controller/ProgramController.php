<?php

namespace App\Controller;


use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Category;
use App\Service\Slugify;
use App\Form\ProgramType;
use App\Form\CategoryType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        if ($form->isSubmitted()) {

            // Deal with the submitted data


            // Get the Entity Manager

            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($program->getTitle());

            $program->setSlug($slug);
            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();

            $email= (new Email())
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

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program->getId()]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $program . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
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
     *  @Route("/{id}/season/{season_id}/episode/{episode_id}", methods={"GET"}, requirements={"id"="\d+"}, name="showEpisode")
     *
     * @return Response
     */
    public function showEpisode(Program $program, Season $season_id, Episode $episode_id): Response
    {
        if (!$episode_id) {
            throw $this->createNotFoundException(
                'No episode with id : ' . $episode_id . ' found in episode\'s table.'
            );
        }
        return $this->render('program/showEpisode.html.twig', [
            'season' => $season_id,
            'program' => $program,
            'episode' => $episode_id,
        ]);
    }
}
