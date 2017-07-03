<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 29/06/2017
 * Time: 13:29
 */

namespace AppBundle\Controller\api;


use AppBundle\Facade\CentreFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CentresController extends Controller
{
    public function getCentresAction($id)
    {
        $parentCentres = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id)->getCentres();
        $centres = (new CentreFacade($this->getDoctrine()->getManager()))->findAll();
        $centresContent = array();
        foreach ($centres as $centre)
        {
            array_push($centresContent,
                [
                    'id'=>$centre->getId(),
                    'name' => $centre->getName(),
                    'isSet' => $parentCentres->contains($centre)
                ]);
        }
        return ResponseFactory::createWebServiceResponse(true, $centresContent);
    }
}