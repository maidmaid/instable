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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    /** @var Instable */
    protected $instable;

    /**
     * @param $user User
     */
    protected function writeUser($user)
    {
        $this->output->write(' '.$this->formatUser($user));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->instable = $this->getContainer()->get('instable');
    }

    /**
     * @param $user User
     *
     * @return string
     */
    protected function formatUser($user)
    {
        $formatted = sprintf('<info>%s</info>', $user->getUsername());
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $formatted =  sprintf('%s (<comment>%s</comment>)', $formatted, $user->getExternalId());
        }

        return $formatted;
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