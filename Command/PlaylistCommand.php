<?php
namespace Hakier\YoutubeBundle\Command;

use Hakier\CoreBundle\Command\AbstractCommand;
use Hakier\YoutubeBundle\Youtube\Playlist;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class YoutubeCommand
 *
 * @package Hakier\YoutubeBundle\Command
 */
class PlaylistCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('youtube:playlist')
            ->setDescription('Youtube')
            ->addArgument(
                'userName',
                InputArgument::OPTIONAL,
                'What playlist do you want to create dirs?'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force to update url in existing playlists?'
            )
            ->addOption(
                'log',
                'l',
                InputOption::VALUE_NONE,
                'Be verbose'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->_setInput($input)
            ->_setOutput($output);

        /**
         * @var Playlist $youtube
         */
        $youtube = $this->getContainer()->get('crawler.youtube');
        $userName = $this->_getOrAskForParam(
            'userName',
            'Enter userName of which playlists you want: '
        );

        $youtube->createPlaylistDirectories(
            $userName,
            $this->_getInput()->getOption('force'),
            $this->_getInput()->getOption('log')
        );
    }
}
