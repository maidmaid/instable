<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tracking;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TrackingController extends Controller
{
    /**
     * @Route("/tracking"), name="app_tracking_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tracker = $em->getRepository('AppBundle:User')->findOneBy(array('username' => 'danymai'));
        $users = $em->getRepository('AppBundle:User')->findAll();
        $trackings = $em->getRepository('AppBundle:Tracking')->findAll();

        return $this->render('tracking/index.html.twig', array(
            'trackings' => $trackings,
            'users' => $users
        ));
    }

    /**
     * @Route("/tracking/{id}/track"), name="app_tracking_track")
     */
    public function trackAction(User $tracked)
    {
        $em = $this->getDoctrine()->getManager();

        $tracker = $em->getRepository('AppBundle:User')->findOneBy(array('username' => 'danymai'));
        $tracking = new Tracking();
        $tracking->setTracker($tracker);
        $tracking->setTracked($tracked);

        $em->persist($tracking);
        $em->flush();

        return $this->redirectToRoute('app_tracking_index');
    }

    /**
     * @Route("/tracking/{id}/untrack"), name="app_tracking_untrack")
     */
    public function untrackAction(User $tracked)
    {
        $em = $this->getDoctrine()->getManager();

        $tracker = $em->getRepository('AppBundle:User')->findOneBy(array('username' => 'danymai'));
        $tracking = $em->getRepository('AppBundle:Tracking')->findOneBy(array('tracker' => $tracker, 'tracked' => $tracked));

        $em->remove($tracking);
        $em->flush();

        return $this->redirectToRoute('app_tracking_index');
    }
}
