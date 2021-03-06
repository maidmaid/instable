<?php

namespace AppBundle\Instable;

use AppBundle\Entity\Relationship;
use AppBundle\Entity\RelationshipRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Event\InstableEvent;
use AppBundle\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\CompleteEvent;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Instaphp\Instagram\Response;
use Instaphp\Instaphp;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Instable extends ContainerAware
{
    /** @var \DateTime */
    protected $start;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var Instaphp */
    protected $api;

    /** @var ClientInterface */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var UnitOfWork */
    protected $uow;

    /** @var Response */
    protected $lastResponse;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->dispatcher = $this->container->get('event_dispatcher');
        $this->uow = $this->em->getUnitOfWork();
        $this->client = new Client();

        $this->api = new Instaphp([
            'client_id' => $container->getParameter('instagram_client_id'),
            'client_secret' => $container->getParameter('instagram_client_secret'),
            'debug' => true,
            'log_path' => $this->container->get('kernel')->getRootDir().'/logs/insta.log',
            /* TODO 'event.error' => null */
            'event.after' => array($this, 'onEventAfter'),
        ]);

        /** @var OAuthToken $token */
        $token = $container->get('security.token_storage')->getToken();
        if ($token instanceof OAuthToken) {
            $this->api->setAccessToken($token->getAccessToken());
        }
    }

    public function onEventAfter(CompleteEvent $e)
    {
        $this->lastResponse = new Response($e->getResponse());
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function getApi()
    {
        return $this->api;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function update($user)
    {
        $this->start = new \DateTime();

        // self update
        $user = $this->updateInfoUser($user);

        // followers + unfollowers
        $this->updateFollows($user);
        $this->updateUnfollows($user);

        // followers by + unfollowers by
        $this->updateFollowedBy($user);
        $this->updateUnfollowedBy($user);

        $this->em->flush();

        $this->dispatcher->dispatch('instable.update.finish', new InstableEvent($user));
    }

    /**
     * @param $data
     *
     * @return User
     */
    public function updateUser($data)
    {
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository('AppBundle:User');

        $user = $repo->findOneByExternalId($data['id']);
        $user = $user === null ? new User() : $user;

        $user->setUsername($data['username']);
        //$user->setBio($data['bio']); // TODO : fix special chars in bio
        $user->setBio('static bio');
        $user->setFullName($data['full_name']);
        $user->setProfilePicture($data['profile_picture']);
        $user->setFullName($data['full_name']);
        $user->setExternalId($data['id']);

        if (array_key_exists('counts', $data)) {
            $user->setCountMedia($data['counts']['media']);
            $user->setCountFollowedBy($data['counts']['followed_by']);
            $user->setCountFollows($data['counts']['follows']);
        }

        $this->em->persist($user);

        return $user;
    }

    /**
     * @param $user User
     *
     * @return User
     */
    public function updateInfoUser($user)
    {
        $this->dispatcher->dispatch('instable.self.start', new InstableEvent($user));
        $r = $this->api->Users->Info($user->getExternalId());
        $user = $this->updateUser($r->data);

        // Compute changes
        $this->uow->computeChangeSets();
        $changeset = $this->uow->getEntityChangeSet($user);

        if (array_key_exists('countMedia', $changeset)) {
            $this->dispatcher->dispatch('instable.self.update_count_media', new InstableEvent($user, $changeset['countMedia']));
        }
        if (array_key_exists('countFollows', $changeset)) {
            $this->dispatcher->dispatch('instable.self.update_count_follows', new InstableEvent($user, $changeset['countFollows']));
        }
        if (array_key_exists('countFollowedBy', $changeset)) {
            $this->dispatcher->dispatch('instable.self.update_count_followed_by', new InstableEvent($user, $changeset['countFollowedBy']));
        }

        return $user;
    }

    /**
     * @param $user User
     *
     * @return Relationship[]
     */
    public function updateFollows($user)
    {
        /* @var Response $response */

        $update = function ($response) use ($user) {
            foreach ($response->data as $d) {
                $targetUser = $this->updateUser($d);
                $relationship = $this->updateRelationship($user, $targetUser);

                if ($relationship->getId() === null) {
                    $this->dispatcher->dispatch('instable.followers.new_follower', new InstableEvent($user, $targetUser));
                }
            }
        };

        $this->dispatcher->dispatch('instable.followers.start', new InstableEvent($user));
        $response = $this->api->Users->Follows($user->getExternalId());
        $update($response);
        while ($response = Utils::nextUrl($response)) {
            $this->dispatcher->dispatch('instable.followers.next_pagination', new InstableEvent($user));
            $update($response);
        }

        $this->em->flush();
    }

    /**
     * @param $targetUser User
     *
     * @return Relationship[]
     */
    public function updateFollowedBy($targetUser)
    {
        /* @var Response $response */

        $update = function ($response) use ($targetUser) {
            foreach ($response->data as $d) {
                $user = $this->updateUser($d);
                $relationship = $this->updateRelationship($user, $targetUser);

                if ($relationship->getId() === null) {
                    $this->dispatcher->dispatch('instable.followers_by.new_follower', new InstableEvent($targetUser, $user));
                }
            }
        };

        $this->dispatcher->dispatch('instable.followers_by.start', new InstableEvent($targetUser));
        $response = $this->api->Users->FollowedBy($targetUser->getExternalId());
        $update($response);
        while ($response = Utils::nextUrl($response)) {
            $this->dispatcher->dispatch('instable.followers_by.next_pagination', new InstableEvent($targetUser));
            $update($response);
        }

        $this->em->flush();
    }

    /**
     * @param $user User
     * @param $targetUser User
     *
     * @return Relationship
     */
    public function updateRelationship($user, $targetUser)
    {
        /** @var RelationshipRepository $repo */
        $repo = $this->em->getRepository('AppBundle:Relationship');
        $relationship = $repo->findOneByLast($user->getId(), $targetUser->getId());

        if ($relationship === null) {
            $relationship = new Relationship($user, $targetUser, true, $this->start, $this->start);
        } elseif (!$relationship->getFollowed()) {
            $relationship = new Relationship($user, $targetUser, true, $this->start, $this->start);
        } elseif ($relationship->getFollowed()) {
            $relationship->setUpdatedAt($this->start);
        }

        $this->em->persist($relationship);

        return $relationship;
    }

    public function updateUnfollows($user)
    {
        /** @var RelationshipRepository $repo */
        $repo = $this->em->getRepository('AppBundle:Relationship');

        $this->dispatcher->dispatch('instable.unfollowers.start', new InstableEvent($user));
        $relationships = $repo->findByUserAndPreUpdatedAt($user, $this->start);
        foreach ($relationships as $relationship) {
            $r = new Relationship($user, $relationship->getTargetUser(), false, $this->start, $this->start);
            $this->em->persist($r);
            $this->dispatcher->dispatch('instable.unfollowers.new_unfollower', new InstableEvent($user, $relationship->getTargetUser()));
        }
    }

    public function updateUnfollowedBy($targetUser)
    {
        /** @var RelationshipRepository $repo */
        $repo = $this->em->getRepository('AppBundle:Relationship');

        $this->dispatcher->dispatch('instable.unfollowers_by.start', new InstableEvent($targetUser));
        $relationships = $repo->findByTargetUserAndPreUpdatedAt($targetUser, $this->start);
        foreach ($relationships as $relationship) {
            $r = new Relationship($relationship->getUser(), $targetUser, false, $this->start, $this->start);
            $this->em->persist($r);
            $this->dispatcher->dispatch('instable.unfollowers_by.new_unfollower', new InstableEvent($targetUser, $relationship->getTargetUser()));
        }
    }
}
