<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{

    protected $dontReport = [AuthorizationException::class, HttpException::class, ModelNotFoundException::class, ValidationException::class,];


    public function report(Exception $e) {
        parent::report($e);

    }


    public function render($request, Exception $e) {
        if(!$request->header('debug')){
            if ($e instanceof ModelNotFoundException) {
                return \App\Http\Controllers\Controller::helpReturnS(false, false, 'Not found resource:'.$e->getMessage());
            }
            if($e->getCode() == 100){
                $controller = new \App\Http\Controllers\Controller();
                return $controller->helpError($e->getMessage());
            }
        }
        return parent::render($request, $e);
    }
}
