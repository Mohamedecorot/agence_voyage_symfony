<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Property;
use App\Entity\PropertySearch;
use App\Form\ContactType;
use App\Form\PropertySearchType;
use App\Notification\ContactNotification;
use App\Repository\PropertyRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PropertyController extends AbstractController
{

    /**
     * @var PropertyRepository
     */
     private $repository;
     private $em;

    public function __construct(PropertyRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        // $property = new Property();
        // $property->setTitle('mon second bien')
        //          ->setPrice(200000)
        //          ->setRooms(4)
        //          ->setBedRooms(3)
        //          ->setDescription('une petite description')
        //          ->setSurface(60)
        //          ->setFloor(4)
        //          ->setHeat(1)
        //          ->setCity('Marseille')
        //          ->setAdress('15 rue Albert Marquet')
        //          ->setPostalCode('13000');
        // $em = $this->getDoctrine()->getManager();
        // $em -> persist($property);
        // $em -> flush();
        // $repository = $this->getDoctrine()->getRepository(Property::class);
        // dump($repository);
        //$property = $this->repository->findAllVisible();
        //dump($property);
        // $property[0]->setSold(true);
        // $this->em->flush();

        $search = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $search);
        $form->handleRequest($request);

        $properties = $paginator->paginate(
            $this->repository->findAllVisibleQuery($search),
            $request->query->getInt('page', 1), 
            12
        );

        return $this->render("property/index.html.twig", [
            'current_menu' => 'properties',
            'properties'   => $properties,
            'form'         => $form->createView()
        ]);
    }

    /**
    * @Route("/biens/{slug}-{id}", name="property.show", requirements={"slug": "[a-z0-9\-]*"})
    * @param Property $property
    * @return Response
    */
    public function show($slug, $id, Request $request, ContactNotification $notification): Response
    {
        $property = $this->repository->find($id);

        if ($property->getSlug() !== $slug) {
            return $this->redirectToRoute('property.show', [
                'id' => $property->getId(),
                'slug' => $property->getSlug()
            ], 301);
        }

        $contact = new Contact();
        $contact->setProperty($property);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification->notify($contact);
            $this->addFlash('success', 'Votre message a bien été envoyé');
            return $this->redirectToRoute('property.show', [
                'id' => $property->getId(),
                'slug' => $property->getSlug()
            ]);
        }

        return $this->render('property/show.html.twig', [
            'property'     => $property,
            'current_menu' => 'properties',
            'form'         =>$form->createView()
        ]);
    }
}