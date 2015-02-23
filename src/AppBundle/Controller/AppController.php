<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/instable/{username}", name="instable")
     */
    public function indexAction(User $user)
    {
        $relationships = $this->getDoctrine()->getRepository('AppBundle:Relationship')->findAllByUser($user);
        $follows = array();
        foreach ($relationships as $relationship) {
            $follows[$relationship->getCreatedAt()->format("Ymdhms")][] = $relationship;
        }

        return $this->render('default/history.html.twig', array(
            'follows' => $follows,
        ));
    }
}
