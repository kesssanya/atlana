<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GitHubSearchService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UsersController extends Controller
{

    /**
     * Количество юзеров на странице
     */
    private const PER_PAGE = 3;

    private const SORTED_FIELDS = [
        'repositories_count',
        'followers',
        'popularity',
    ];

    private const DEFAULT_SORT_FIELD = 'repositories_count';
    private const DEFAULT_SORT_ORDER = 'desc';

    /**
     * Список всех пользователей на странице
     */
    public function index(Request $request): Paginator
    {
        $sort = in_array($request->sort, self::SORTED_FIELDS, true) ? $request->sort : self::DEFAULT_SORT_FIELD;
        $order = in_array($request->order, ['asc', 'desc'], true) ? $request->order : self::DEFAULT_SORT_ORDER;
        return User::select(['id', 'avatar', 'name', 'repositories_count'])
            ->orderBy($sort, $order)
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Вывод пользователя
     *
     * @param int $id
     * @return User
     */
    public function show(int $userId): User
    {
        $user = User::find($userId);
        $user->incrementPopularity();
        $user->save();
        return $user;
    }

    /**
     * Поиск по гитлабцу
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection
     */
    public function search(Request $request): Paginator
    {
        $q = (string)$request->get('q');
        if (empty($q)) {
            throw new AccessDeniedHttpException('Get parameter q cannot be empty');
        }
        $usersId = (new GitHubSearchService())->getUsersID($q);
        if (empty($usersId)) {
            return new Collection();
        }

        $sort = in_array($request->sort, self::SORTED_FIELDS, true) ? $request->sort : self::DEFAULT_SORT_FIELD;
        $order = in_array($request->order, ['asc', 'desc'], true) ? $request->order : self::DEFAULT_SORT_ORDER;
        return User::whereIn('id', $usersId)
            ->select(['id', 'avatar', 'name', 'repositories_count'])
            ->orderBy($sort, $order)
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Топ 3 самых популярных пользователей по просмотру
     *
     * @return Collection
     */
    public function top3(): Collection
    {
        return User::orderBy('popularity', 'desc')->take(3)->get();
    }
}
