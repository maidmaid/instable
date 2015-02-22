<?php

namespace AppBundle\Command;

use AppBundle\Entity\Tracking;
use AppBundle\Entity\User;
use AppBundle\Event\InstableEvent;
use AppBundle\Instable\Instable;
use Instaphp\Exceptions\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

        while (true) {
            $trackings = $this->getContainer()->get('doctrine')->getRepository('AppBundle:Tracking')->findAll();

            foreach ($trackings as $tracking) {
                try {
                    $this->instable->update($tracking->getTracked());
                } catch (Exception $e) {
                    $this->output->writeln(sprintf("<error>%s, code %s</error>", $e->getMessage(), $e->getCode()));
                    $this->sleep(15);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'instable.self.start' => 'onSelfStart',
            'instable.self.update_count_media' => 'onSelfUpdateCountMedia',
            'instable.self.update_count_follows' => 'onSelfUpdateCountFollows',
            'instable.self.update_count_followed_by' => 'onSelfUpdateCountFollowedBy',
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
            'instable.update.finish' => 'onUpdateFinish',
        );
    }

    public function onSelfStart(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->writeUser($e->getUser());
            $this->output->write(' <question>start self</question>');
        }
    }

    public function onSelfUpdateCountMedia(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s has <info>%s</info> medias, before <info>%s</info>',
            $this->formatUser($e->getUser()),
            $e->getSubject()[1],
            $e->getSubject()[0]
        ));
    }

    public function onSelfUpdateCountFollows(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s follows <info>%s</info>, before <info>%s</info>',
            $this->formatUser($e->getUser()),
            $e->getSubject()[1],
            $e->getSubject()[0]
        ));
    }

    public function onSelfUpdateCountFollowedBy(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s is followed by <info>%s</info>, before <info>%s</info>',
            $this->formatUser($e->getUser()),
            $e->getSubject()[1],
            $e->getSubject()[0]
        ));
    }

    public function onFollowersStart(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write(' <question>start followers</question>');
        }
    }

    public function onFollowersNextPagination(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write('.');
        }
    }

    public function onFollowersNewFollower(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s follows %s',
            $this->formatUser($e->getUser()),
            $this->formatUser($e->getSubject())
        ));
    }

    public function onUnfollowersStart(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write(' <question>start unfollowers</question>');
        }
    }

    public function onUnfollowersNewUnfollower(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s no longer follows %s',
            $this->formatUser($e->getUser()),
            $this->formatUser($e->getSubject())
        ));
    }

    public function onFollowersByStart(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write(' <question>start followers by</question>');
        }
    }

    public function onFollowersByNextPagination(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write('.');
        }
    }

    public function onFollowersByNewFollower(InstableEvent $e)
    {
        $this->write(sprintf(
            '%s is followed by %s',
            $this->formatUser($e->getUser()),
            $this->formatUser($e->getSubject())
        ));
    }

    public function onUnfollowersByStart(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write(' <question>start unfollowers</question>');
        }
    }

    public function onUnfollowersByNewUnfollower(InstableEvent $e)
    {
        $this->write(sprintf(
            "%s is no longer new unfollower %s",
            $this->formatUser($e->getUser()),
            $this->formatUser($e->getSubject())
        ));
    }

    public function onUpdateFinish(InstableEvent $e)
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write(sprintf(' <info>%s</info> remaining', $this->instable->getLastResponse()->remaining));
        }
    }

    /**
     * @param $user User
     */
    protected function writeUser($user)
    {
        $this->output->write(' '.$this->formatUser($user));
    }

    /**
     * @param $user User
     * @return string
     */
    protected function formatUser($user)
    {
        return sprintf('<info>%s</info> (<comment>%s</comment>)', $user->getUsername(), $user->getExternalId());
    }

    protected function sleep($seconds)
    {
        $this->output->write(sprintf("Sleep <info>%s</info> seconds", $seconds));
        $bar = new ProgressBar($this->output, $seconds);
        for ($s = 0; $s < $seconds; $s++) {
            sleep(1);
            $bar->advance();
        }
    }

    protected function write($message)
    {
        $this->output->write(sprintf(' %s <fg=magenta;options=bold>|</fg=magenta;options=bold>', $message));
    }
}
