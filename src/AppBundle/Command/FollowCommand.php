<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Instable\Instable;
use Instaphp\Exceptions\Exception;
use Symfony\Component\Console\Input\InputArgument;
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
        $this
            ->setName('instable:follow')
            ->addArgument('username', InputArgument::REQUIRED, 'user who follow other');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        /** @var User $user */
        $user = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findOneByUsername($input->getArgument('username'));

        $api = $this->getContainer()->get('instable')->getApi();
        $api->setAccessToken($user->getAccessToken());

        while (true) {
            $likes = $this->getPopularLikes();
            foreach ($likes->data as $data) {
                $user = $this->instable->updateUser($data);

                try {
                    $user = $this->instable->updateInfoUser($user);
                } catch (Exception $e) {
                    $this->output->writeln(sprintf('<info>%s</info> -> <error>%s</error>', $data['username'], $e->getMessage()));
                    continue;
                }

                if ($this->isGoodUser($user)) {
                    $this->follow($user);
                    break;
                }
            }
        }
    }

    public function getPopularLikes()
    {
        $api = $this->getContainer()->get('instable')->getApi();
        $popular = $api->Media->Popular();
        $media = $popular->data[0];
        $likes = $api->Media->Likes($media['id']);
        $this->output->writeln(sprintf('likes of media <info>%s</info>', $media['id']));

        return $likes;
    }

    public function follow($user)
    {
        try {
            $f = $this->getContainer()->get('instable')->getApi()->Users->Follow($user->getExternalId());
            $this->output->writeln(sprintf(
                ' -> status: <info>%s</info>, private user: <info>%s</info>',
                $f->data['outgoing_status'],
                $f->data['target_user_is_private'] ? 'yes' : 'no'
            ));
        } catch (Exception $e) {
            $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }

    public function isGoodUser($user)
    {
        $this->writeUser($user);

        // Quota < 1
        $q = $user->getCountFollows() / ($user->getCountFollowedBy() + 1);
        $this->output->writeln(sprintf(
            ' -> <question>%s%%</question> (%s / %s)',
            round($q * 100),
            $user->getCountFollows(),
            $user->getCountFollowedBy()
        ));

        return $q < 1;
    }
}
