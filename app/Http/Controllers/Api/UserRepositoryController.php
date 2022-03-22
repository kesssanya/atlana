<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repository;
use App\Models\User;
use App\Services\GitHubSearchService;
use Illuminate\Http\Request;

class UserRepositoryController extends Controller
{

    private const PER_PAGE = 3;

    public function index(int $userId, Request $request)
    {
        $user = User::find($userId);
        $service = new GitHubSearchService();
        $ids = $service->getRepositoriesIdForUser($user->login, $request->q ?? '');
        if (!$ids) {
            return [];
        }
        return Repository::whereIn('id', $ids)->paginate(self::PER_PAGE)->withQueryString();
    }
}
