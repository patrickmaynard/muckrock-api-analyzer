<?php

namespace App\Command;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PostsDeleteCommand extends Command
{
    protected static $defaultName = 'app:posts:delete';
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes all posts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->removeAllPosts();

        $io->success('Posts successfully deleted.');

        return 0;
    }

    protected function removeAllPosts()
    {
        $postRepository = $this
            ->entityManager
            ->getRepository(Post::class)
        ;
        $posts = $postRepository->findAll();
        foreach ($posts as $post) {
            $this->entityManager->remove($post);
        }
        $this->entityManager->flush();
    }
}
