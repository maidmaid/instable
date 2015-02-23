<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use AppBundle\Utils\Utils;
use Instaphp\Exceptions\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FollowCommand extends AbstractCommand
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
        parent::execute($input, $output);

        /** @var User $user */
        $user = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:User')->findOneByExternalId('1718874928');
        $api = $this->getContainer()->get('instable')->getApi();
        $api->setAccessToken($user->getAccessToken());

        $follow = function ($response) use ($api, $output) {
            foreach ($response->data as $d) {
                // TODO
                $user = $this->instable->updateUser($d);
                try {
                    $user = $this->instable->updateInfoUser($user);
                } catch (Exception $e) {
                    $output->writeln(sprintf('<info>%s</info> -> <error>%s</error>', $d['username'], $e->getMessage()));
                    continue;
                }
                $this->writeUser($user);

                $q = $user->getCountFollows() / $user->getCountFollowedBy();
                if ($q < 1) {
                    $output->writeln(sprintf(' -> <error>%s%%</error> (%s / %s)', round($q * 100), $user->getCountFollows(), $user->getCountFollowedBy()));
                    sleep(1);
                    continue;
                }

                try {
                    $f = $api->Users->Follow($user->getExternalId());
                    $output->writeln(sprintf(
                        ' -> status: <info>%s</info>, private user: <info>%s</info>',
                        $f->data['outgoing_status'],
                        $f->data['target_user_is_private'] ? 'yes' : 'no'
                    ));
                } catch (Exception $e) {
                    $output->writeln($e->getMessage());
                }
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
