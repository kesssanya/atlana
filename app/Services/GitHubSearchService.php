<?php

namespace App\Services;

use App\Models\Repository;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Nette\Utils\Image;
use Symfony\Component\HttpKernel\Exception\HttpException;
use RuntimeException;

class GitHubSearchService
{

    private const BASE_URL = 'https://api.github.com';

    private const PER_PAGE = 9;

    private const CACHE_TTL = 300;

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        return new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 10,
        ]);
    }

    /**
     * @param string $q
     * @return array
     */
    public function getUsersID(string $q): array
    {
        $hash = md5("users_{$q}");
        $cached = Cache::get($hash);
        if ($cached) {
            return $cached;
        }
        $findUsers = $this->searchUsers($q);
        $ids = array_map(function ($userData) {
            $user = $this->getUser($userData['login']);
            return $user->id;
        }, $findUsers['items']);
        Cache::add($hash, $ids, self::CACHE_TTL);
        return $ids;
    }

    /**
     * @param string $q
     * @return array
     */
    protected function searchUsers(string $q): array
    {
        try {
            $query = [
                'q' => $q,
                'per_page' => self::PER_PAGE,
                'sort' => 'repositories',
                'order' => 'desc',
            ];
            $url = '/search/users';
            $client = $this->getClient();
            $response = $client->get($url, ['query' => http_build_query($query)]);
            $json = $response->getBody();
            $this->log($url, $query, $json);
            return json_decode($json, true);
        } catch (GuzzleException $e) {
            throw new HttpException(403, "Gitlab: " . $e->getMessage());
        }
        return [];
    }

    /**
     * @param string $userLogin
     * @return User
     * @throws GuzzleException
     */
    protected function getUser(string $userLogin): User
    {
        $client = $this->getClient();
        $url = sprintf("/users/%s", $userLogin);
        $response = $client->get($url);
        $json = $response->getBody();
        $this->log($url, [], $json);
        $userData = json_decode($json, true);
        return $this->mapToUser($userData);

    }

    /**
     * @param array $userData
     * @return User
     */
    protected function mapToUser(array $userData): User
    {
        $user = User::firstOrNew(['id' => $userData['id']]);
        $user->login = $userData['login'];
        $user->name = $userData['name'] ?? $userData['login'];
        $user->email = $userData['email'] ?? null;
        $user->followers = (int)$userData['followers'];
        $user->following = (int)$userData['following'];
        $user->repositories_count = (int)$userData['public_repos'];
        $user->created_at = $userData['created_at'];
        $user->updated_at = $userData['updated_at'];
        $user->location = $userData['location'] ?? null;
        $user->bio = $userData['bio'] ?? null;

        // сохраняем аватарку
        if (!empty($userData['avatar_url']) && empty($user->avatar)) {
            $content = file_get_contents($userData['avatar_url']);
            $type = Image::detectTypeFromString($content);
            $ext = Image::typeToExtension($type ?? IMAGETYPE_PNG);
            $user->avatar = "avatars/{$user->login}.$ext";
            if (!Storage::put($user->avatar, $content)) {
                throw new RuntimeException('Cant save avatar');
            }
        }
        $user->save();
        return $user;
    }

    /**
     * @param string $login
     * @param string $q
     * @return array
     */
    public function getRepositoriesIdForUser(string $login, string $q = ''): array
    {
        $hash = md5("repos_{$login}_{$q}");
        $cache = Cache::get($hash);
        if (!is_null($cache)) {
            return $cache;
        }
        // чистим юзера из строки поиска
        $q = preg_replace('/(.*)\s?user\:[\w]+\s?(.*)/i', '$1$2', $q);
        $q = trim($q);
        if (empty($q)) {
            $repositories = $this->getRepositoriesForUser($login);
        } else {
            $q = "{$q} user:{$login}";
            $repositories = $this->searchRepositories($q);
            $repositories = $repositories['items'] ?? [];

        }
        $ids = array_map(function ($data) {
            return $this->mapToRepository($data)->id;
        }, $repositories);
        Cache::put($hash, $ids, self::CACHE_TTL);
        return $ids;
    }

    /**
     * @param string $login
     * @return mixed
     */
    protected function getRepositoriesForUser(string $login)
    {
        try {
            $url = sprintf("/users/%s/repos", $login);
            $query = [
                'per_page' => self::PER_PAGE,
                'sort' => 'updated',
                'order' => 'desc',
            ];
            $client = $this->getClient();
            $response = $client->get($url, ['query' => http_build_query($query)]);
            $json = $response->getBody();
            $this->log($url, $query, $json);
            return json_decode($json, true);
        } catch (GuzzleException $e) {
            throw new HttpException(403, "Gitlab: " . $e->getMessage());
        }
    }

    /**
     * @param $q
     * @return array
     */
    protected function searchRepositories($q): array
    {
        try {
            $query = [
                'q' => $q,
                'per_page' => self::PER_PAGE,
                'sort' => 'updated',
                'order' => 'desc',
            ];
            $client = $this->getClient();
            $url = '/search/repositories';
            $response = $client->get($url, ['query' => http_build_query($query)]);
            $json = $response->getBody();
            $this->log($url, $query, $json);
            return json_decode($json, true);
        } catch (GuzzleException $e) {
            throw new HttpException(403, "Gitlab: " . $e->getMessage());
        }
        return [];
    }

    /**
     * @param array $data
     * @return Repository
     */
    protected function mapToRepository(array $data): Repository
    {
        $repository = Repository::firstOrNew(['id' => $data['id']]);
        $repository->name = $data['name'];
        $repository->user_id = $data['owner']['id'];
        $repository->forks = (int)$data['forks'];
        $repository->stars = (int)$data['stargazers_count'];
        $repository->created_at = $data['created_at'];
        $repository->updated_at = $data['updated_at'];
        $repository->save();
        return $repository;
    }

    /**
     * @param string $url
     * @param array $request
     * @param string $response
     * @return void
     */
    protected function log(string $url, array $request, string $response)
    {
        $message = PHP_EOL . "URL: " . self::BASE_URL . $url . PHP_EOL;
        $message .= "GET: " . print_r($request, true);
        $message .= "RESPONSE: " . $response . PHP_EOL;
        Log::channel('github_search')->debug($message);
    }

}
