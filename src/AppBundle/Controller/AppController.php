<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    /**
     * @Route("/instable/{username}", name="instable")
     */
    public function indexAction(User $user)
    {
        $relationships = $this->getDoctrine()->getRepository('AppBundle:Relationship')->findAllByUser($user);
        $follows = array();
        foreach($relationships as $relationship)
        {
            $follows[$relationship->getCreatedAt()->format("Ymdhms")][] = $relationship;
        }
        
        $relationships = $this->getDoctrine()->getRepository('AppBundle:Relationship')->findAllByTargetUser(1);
        $followedBy = array();
        foreach($relationships as $relationship)
        {
            $followedBy[$relationship->getCreatedAt()->format("Ymdhms")][] = $relationship;
        }

        return $this->render('default/index.html.twig', array(
            'follows' => $follows,
            'followedBy' => $followedBy,
        ));
    }
}
