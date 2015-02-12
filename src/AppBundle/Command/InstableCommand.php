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

    /** @var Instable */
    protected $instable;

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
        $this->instable = $this->getContainer()->get('instable');
        $this->instable->getDispatcher()->addSubscriber($this);

        while(true)
        {
            $this->instable->update('198130716');   // mnebil
            $this->instable->update('274407715');   // danymai
            $this->instable->update('432990605');   // mirsad_ddd
            $this->instable->update('28441574');    // mathieu_couturier
            $this->instable->update('216991190');   // odrey_0202
            $this->instable->update('1544096656');  // aminoush_dolce
            $this->instable->update('197151608');   // bi_lit
            $this->instable->update('1541530119');  // gwen.gwen.gwen
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
            'instable.unfollowers_by.new_unfollower' => 'onUnfollowersByNewUnfollower',
            'instable.update.finish' => 'onUpdateFinish'
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
        $this->bar->setBarCharacter('<comment>=</comment>');
        $this->bar->setEmptyBarCharacter(' ');
        $this->bar->setProgressCharacter('<comment>></comment>');
        $this->bar->advance();
        $this->writeUser($user);
        $this->output->write(" changes : ");
        dump($changeset);
    }

    public function onFollowersStart(Event $e)
    {
        $this->output->writeln("Update followers");
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

    public function onUpdateFinish(Event $e)
    {
        $this->output->writeln(sprintf("\nremaining : %s\nUpload finished!", $this->instable->getLastResponse()->remaining));
    }

    /**
     * @param $user User
     */
    protected function writeUser($user)
    {
        $this->output->write(sprintf(' <info>%s</info> (<comment>%s</comment>)', $user->getUsername(), $user->getExternalId()));
    }
}
