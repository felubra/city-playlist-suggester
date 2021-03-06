<?php

namespace App\Services;

use App\Exception\InvalidInputException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Serviço que gera uma playlist com base num gênero musical, utilizando-se da API do Spotify.
 */
class SpotifyPlaylistGetter
{
    /**
     * O cliente para a API do Spotify.
     *
     * @var SpotifyClient
     */
    private $spotifyClient;

    /**
     * Uma implementação de um adaptador de contrato de cache do Symfony.
     *
     * @var AdapterInterface
     */
    private $cache;

    const SPOTIFY_RECOMMENDATIONS_ENDPOINT = 'https://api.spotify.com/v1/recommendations';

    /**
     * Constrói uma instância do serviço SpotifyPlaylistGetter.
     *
     * @param SpotifyClient    $spotifyClient O cliente para a API do Spotify
     * @param AdapterInterface $cache      uma implementação de um adaptador de contrato de cache do Symfony
     *
     * @throws InvalidInputException
     */
    public function __construct(
        SpotifyClient $spotifyClient,
        AdapterInterface $cache
    ) {
        $this->spotifyClient = $spotifyClient;
        $this->cache = $cache;
    }

    /**
     * Gera uma playlist no formato Artista(s) - Título com as músicas recomendadas para
     * um determinado gênero musical.
     *
     * @param string $genre O gênero musical
     *
     * @return Array[string]
     */
    public function getPlaylistForGenre(string $genre)
    {
        return $this->cache->get("genre-$genre", function (ItemInterface $item) use ($genre) {
            $response = $this->spotifyClient->authenticatedRequest('GET', self::SPOTIFY_RECOMMENDATIONS_ENDPOINT, [
                'query' => [
                    'seed_genres' => $genre,
                    'target_popularity' => 70,
                ],
            ])->toArray();
            $item->expiresAfter(600); //TODO: parametrizar

            // Transforma a resposta da API numa lista no formato Artista(s) - Título
            return array_map(function ($track) {
                $title = $track['name'];
                // Separa os artistas por vírgula
                $artists = implode(', ', array_map(function ($artist) {
                    return $artist['name'];
                }, $track['artists']));

                return "{$artists} - $title";
            }, $response['tracks']);
        });
    }
}
