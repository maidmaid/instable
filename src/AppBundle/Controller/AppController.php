<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
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
        $url = $this->get('instable')->getApi()->getOauthUrl();

        return $this->redirect($url);
    }

    /**
     * @Route("/oauth", name="oauth")
     */
    public function oauthAction(Request $request)
    {
        $instable = $this->get('instable');
        $em = $this->getDoctrine()->getEntityManager();

        // Authorize
        $code = $request->query->get('code');
        $instable->getApi()->Users->Authorize($code);

        // Save user
        $accessToken = $instable->getApi()->getAccessToken();
        $data = $this->get('instable')->getApi()->Users->getCurrentUser();
        $user = $instable->updateUser($data);
        $user->setCode($code);
        $user->setAccessToken($accessToken);

        $em->persist($user);
        $em->flush();

        return $this->render('default/index.html.twig', array('user' => $user));
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
