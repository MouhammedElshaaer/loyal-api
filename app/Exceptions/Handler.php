<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use App\Http\Traits\ResponseUtilities;

class Handler extends ExceptionHandler
{
    use ResponseUtilities;

    private $data;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    // public function __construct(){

    //     $this->data = [
    //         "code"=> null,
    //         "message"=>"",
    //         "data" => new \stdClass()
    //     ];

    // }

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];
        $this->initErrorResponse($exception);
        return response()->json($this->data, 200);
            
        // return parent::render($request, $exception);
    }
}
