<?php

namespace Hakier\YoutubeBundle\Youtube;

use Goutte\Client;
use Hakier\CoreBundle\Traits\Template;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Translation\IdentityTranslator;

/**
 * Class Youtube
 *
 * @Service("youtube.playlist")
 *
 * @package Hakier\YoutubeBundle\Service
 */
class Playlist
{
    use Template;

    const URL_USER_PLAYLISTS = 'https://www.youtube.com/user/%s/playlists';

    /**
     * @InjectParams({
     *      "translator" = @Inject("translator")
     * })
     *
     * @param IdentityTranslator $translator
     */
    public function __construct(IdentityTranslator $translator)
    {
        $this->_translator = $translator;
    }

    /**
     * @param string $userName
     *
     * @return array
     */
    public function getPlaylists($userName)
    {
        $client = new Client();

        $crawler = $client->request(
            'GET',
            sprintf(self::URL_USER_PLAYLISTS, $userName)
        );

        $playlists = [];

        $crawler->filter('.yt-lockup-title a')->each(
            function (Crawler $title) use (&$playlists) {
                $id = preg_replace('/^[^=]*=/', null, $title->attr('href'));

                $playlists[$id] = [
                    'id'    => $title->attr('href'),
                    'title' => $title->text()
                ];
            }
        );

        return $playlists;
    }

    /**
     * @param string $userName
     * @param bool   $modifyExistingPlaylist
     *
     * @return string
     */
    public function createPlaylistDirectories($userName, $modifyExistingPlaylist = false, $verbose = false)
    {
        $log = null;
        $logTemplate = "Directory:\t\t{directory}\n"
            . "Was created:\t\t{wasCreated}\n"
            . "Url file:\t\t{urlFile}\n"
            . "Url file:\t\t{urlFile}\n"
            . "Playlist url:\t\t{playlistUrl}\n"
            . "Playlist url content:\t{playlistUrlContent}\n"
            . str_repeat('=', 20) . "\n";

        $playlistDir = sprintf(
            '/home/Youtube/playlist/%s',
            $userName
        );

        foreach ($this->getPlaylists($userName) as $playlist) {
            $title = $playlist['title'];

            $dirName = $playlistDir . '/' . $title;
            $isDir   = is_dir($dirName);

            if (!$modifyExistingPlaylist && is_dir($dirName)) {
                continue;
            }

            if (!$isDir) {
                mkdir($dirName, 0777, true);
            }

            $urlFile     = sprintf('%s/.url', $dirName);
            $playlistUrl = sprintf('http://youtube.com%s', $playlist['id']);

            file_put_contents(
                $urlFile,
                $playlistUrl
            );

            if ($verbose) {
                $log .= $this->_template(
                    $logTemplate,
                    [
                        '{directory}'          => $dirName,
                        '{wasCreated}'         => !$isDir ? 'Yes' : 'No',
                        '{urlFile}'            => $urlFile,
                        '{playlistUrl}'        => $playlistUrl,
                        '{playlistUrlContent}' => file_get_contents($urlFile)
                    ]
                );

                echo $log;
            }
        }

        return $log;
    }
}
