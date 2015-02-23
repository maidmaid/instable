<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use AppBundle\Utils\Utils;
use Instaphp\Exceptions\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FollowCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    /** @var Instable */
    protected $instable;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('instable:follow');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var User $user */
        $user = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:User')->findOneByExternalId('1718874928');
        $api = $this->getContainer()->get('instable')->getApi();
        $api->setAccessToken($user->getAccessToken());

        $follow = function ($response) use ($api, $output) {
            foreach ($response->data as $d) {
                try {
                    $api->Users->Follow($d['id']);
                } catch (Exception $e) {
                    $output->writeln($e->getMessage());
                }
                $output->writeln($d['id']);
                sleep(180);
            }
        };

        $response = $api->Users->Follows('25025320');
        $follow($response);
        while ($response = Utils::nextUrl($response)) {
            $follow($response);
        }
    }
}
