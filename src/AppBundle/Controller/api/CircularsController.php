<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 17/06/2017
 * Time: 12:20
 */

namespace AppBundle\Controller\api;


use AppBundle\Facade\CircularFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CircularsController extends Controller
{
    public function getCircularsAction($id)
    {
        $circular = (new CircularFacade($this->getDoctrine()->getManager()))->find($id);
        $circularAttachment = count($circular->getAttachments()) == 0 ? null : $circular->getAttachments()[0];
        return ResponseFactory::createWebServiceResponse(true, [
            'subject' => $circular->getSubject(),
            'message' => $circular->getMessage(),
            'sendingDate' => $circular->getSendingDate()->format('Y-m-d H:i:s'),
            'attachmentId' => $circularAttachment == null ? null : $circularAttachment->getId(),
            'attachmentName' => $circularAttachment == null ? null : $circularAttachment->getName()
        ]);
    }
}