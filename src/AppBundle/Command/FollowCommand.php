<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use AppBundle\Utils\Utils;
use Instaphp\Exceptions\Exception;
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

        $follow = function ($data) use ($api, $output) {
            $user = $this->instable->updateUser($data);

            // Try get infos
            try {
                $user = $this->instable->updateInfoUser($user);
            } catch (Exception $e) {
                $output->writeln(sprintf('<info>%s</info> -> <error>%s</error>', $data['username'], $e->getMessage()));
                return;
            }
            $this->writeUser($user);

            // Quota < 1
            $q = $user->getCountFollows() / ($user->getCountFollowedBy() + 1);
            $output->writeln(sprintf(' -> <question>%s%%</question> (%s / %s)', round($q * 100), $user->getCountFollows(), $user->getCountFollowedBy()));
            if ($q < 1) {
                return;
            }

            // Follow
            try {
                $f = $api->Users->Follow($user->getExternalId());
                $output->writeln(sprintf(
                    ' -> status: <info>%s</info>, private user: <info>%s</info>',
                    $f->data['outgoing_status'],
                    $f->data['target_user_is_private'] ? 'yes' : 'no'
                ));
                $this->sleep(180);
            } catch (Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        };

        while (true) {
            $popular = $api->Media->Popular();
            foreach ($popular->data as $p) {
                $likes = $api->Media->Likes($p['id']);
                foreach ($likes->data as $user) {
                    $follow($user);
                }
            }
        }
    }
}
