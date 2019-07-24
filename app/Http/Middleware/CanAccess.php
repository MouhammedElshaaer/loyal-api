<?php

namespace App\Http\Middleware;

use App\Http\Traits\ResponseUtilities;

use Closure;

class CanAccess
{
    use ResponseUtilities;

    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        
        $authorize = false;
        foreach (auth()->user()->roles as $userRole) {
            foreach ($roles as $role) {
                if ($userRole->name == config('constants.roles.'.$role)) { $authorize=true; }
            }
        }

        if (!$authorize) {
            $this->initResponse(400, 'unauthorized');
            return response()->json($this->data, 200);
        }else { return $next($request); }

    }
}
