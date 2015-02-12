<?php

namespace AppBundle\Command;

use AppBundle\Entity\History;
use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use Instaphp\Exceptions\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InstableCommand extends ContainerAwareCommand implements EventSubscriberInterface
{
    /** @var ProgressBar */
    protected $bar;

    /** @var OutputInterface */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('instable:update')
            ->setDescription('instable command')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $instable = $this->getContainer()->get('instable');
        $instable->getDispatcher()->addSubscriber($this);

        while(true)
        {
            //$instable->update('432990605'); // mirsad_ddd
            //$instable->update('274407715'); // mathieu_couturier
            $instable->update('274407715');  // danymai
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::EXCEPTION => 'onConsoleException',
            'instable.self.start' => 'onSelfStart',
            'instable.self.update' => 'onSelfUpdate',
            'instable.followers.start' => 'onFollowersStart',
            'instable.followers.next_pagination' => 'onFollowersNextPagination',
            'instable.followers.new_follower' => 'onFollowersNewFollower',
            'instable.unfollowers.start' => 'onUnfollowersStart',
            'instable.unfollowers.new_unfollower' => 'onUnfollowersNewUnfollower',
            'instable.followers_by.start' => 'onFollowersByStart',
            'instable.followers_by.next_pagination' => 'onFollowersByNextPagination',
            'instable.followers_by.new_follower' => 'onFollowersByNewFollower',
            'instable.unfollowers_by.start' => 'onUnfollowersByStart',
            'instable.unfollowers_by.new_unfollower' => 'onUnfollowersByNewUnfollower'
        );
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $e = $event->getException();

        if($e instanceof Exception)
        {
            $this->output->writeln(sprintf("HTTP code response : <error>%s</error>", $e->getCode()));
        }
    }

    public function onSelfStart(Event $e)
    {
        $this->output->writeln("\nSelf update");
    }

    public function onSelfUpdate(GenericEvent $e)
    {
        /** @var User $user */
        $user = $e->getSubject()['user'];
        $changeset = $e->getSubject()['changeset'];

        $max = Instable::estimateOperations($user);
        $this->bar = new ProgressBar($this->output, $max);
        $this->bar->advance();
        $this->writeUser($user);
        $this->output->write(sprintf(" (<comment>%s</comment>)", $user->getExternalId()));
        $this->output->write(" changes : ");
        dump($changeset);
    }

    public function onFollowersStart(Event $e)
    {
        $this->output->writeln("\nUpdate followers");
        $this->bar->advance();
    }

    public function onFollowersNextPagination(Event $e)
    {
        $this->output->writeln('');
        $this->bar->advance();
    }

    public function onFollowersNewFollower(GenericEvent $e)
    {
        $this->writeUser($e->getSubject());
    }

    public function onUnfollowersStart(Event $e)
    {
        $this->output->writeln("\nUpdate unfollowers");
        $this->bar->advance();
    }

    public function onUnfollowersNewUnfollower(GenericEvent $e)
    {
        $this->writeUser($e->getSubject());
    }

    public function onFollowersByStart(Event $e)
    {
        $this->output->writeln("\nUpdate followers by");
        $this->bar->advance();
    }

    public function onFollowersByNextPagination(Event $e)
    {
        $this->output->writeln('');
        $this->bar->advance();
    }

    public function onFollowersByNewFollower(GenericEvent $e)
    {
        $this->writeUser($e->getSubject());
    }

    public function onUnfollowersByStart(Event $e)
    {
        $this->output->writeln("\nUpdate unfollowers by");
        $this->bar->advance();
    }

    public function onUnfollowersByNewUnfollower(GenericEvent $e)
    {
        $this->writeUser($e->getSubject());
    }

    /**
     * @param $user User
     */
    protected function writeUser($user)
    {
        $this->output->write(sprintf(' <info>%s</info>', $user->getUsername()));
    }
}