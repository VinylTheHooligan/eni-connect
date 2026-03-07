<?php

namespace App\Controller\Api;

use App\Entity\Campus;
use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class APIPlaceController extends AbstractController
{
    #[Route('/places/by-campus/{id}', name: 'places_by_campus')]
    public function byCampus(Campus $campus, PlaceRepository $placeRepository): Response
    {
        $places = $placeRepository->findBy(['campus' => $campus]);

        $data = array_map(fn($p) => [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'city' => $p->getCity()->getName(),
            'street' => $p->getStreet(),
            'postalCode' => $p->getCity()->getPostalCode(),
            'latitude' => $p->getLatitude(),
            'longitude' => $p->getLongitude(), 
        ], $places);

        return new JsonResponse($data);
    }
}
