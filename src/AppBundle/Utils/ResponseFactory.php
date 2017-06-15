<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 17/04/2017
 * Time: 20:33
 */

namespace AppBundle\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseFactory
{

    public static function createJsonResponse($success, $content)
    {
        return $success ?
            new JsonResponse([
                'success' => true,
                'content' => $content,
            ]) :
            new JsonResponse([
                'success' => false,
                'error' => $content,
            ]);
    }

    public static function createWebServiceResponse($success, $content)
    {
        return $success ?
            [
                'success' => true,
                'content' => $content,
            ] :
            [
                'success' => false,
                'error' => $content,
            ];
    }
}