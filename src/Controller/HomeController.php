<?php
namespace App\Controller;

use App\Repository\PropertyRepository;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;


class HomeController
{
    /**
     * @var twig\Environment
     */

    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function index(PropertyRepository $repository): Response
    {
        $properties = $repository->findLatest();
        return new Response($this->twig->render('pages/home.html.twig',[
            'properties' => $properties
        ]));
    //     return new Response($this->twig->render('pages/home.html.twig',[
    //         'properties' => $properties
    //     ]);
    }

}