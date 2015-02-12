<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        return $this->render('default/index.html.twig', array(
            'follows' => $follows
        ));
    }

    /**
     * @Route("/oauth", name="oauth")
     */
    public function oauthAction(Request $request)
    {
        $url = $this->get('instable')->getApi()->getOauthUrl();
        $url = str_replace('response_type=code', 'response_type=token', $url);
        return $this->render('default/oaut.html.twig', array('url' => $url));
    }
}
