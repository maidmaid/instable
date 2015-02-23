<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
}
