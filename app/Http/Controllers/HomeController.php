<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Flash;
use App\Repositories\RouteRepository as Route;
use App\Repositories\PermissionRepository as Permission;
use Auth;

class HomeController extends Controller
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @param Route $route
     * @param Permission $permission
     */
    public function __construct(Route $route, Permission $permission)
    {
        $this->route = $route;
        $this->permission = $permission;
    }

    public function index() {

        $homeRouteName = 'login';

        try {
            $user = Auth::user();

            if ($user) {
                $homeCandidateName = config('app.home_route');
                $homeRoute         = $this->route->findBy('name', $homeCandidateName);
                $homePerm          = $homeRoute->permission;
                if ($user->can($homePerm->name)) {
                    $homeRouteName = $homeCandidateName;
                } else {
                    // TODO: create check if the user is outlet owner,
                    // and redirect to it's dashboard.
                    $homeRouteName = 'login';
                }
            }
            // $homeCandidateName = config('app.home_route');
            // $homeRouteName = $homeCandidateName;
        }
        catch (\Exception $ex) { } // Eat the exception will default to the welcome route.

        return \Redirect::route($homeRouteName);
    }
}
