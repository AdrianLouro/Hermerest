<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 20/06/2017
 * Time: 14:20
 */

namespace AppBundle\Controller\api;


use AppBundle\Facade\AuthorizationFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationsController extends Controller
{
    public function getParentAuthorizationAction($parentId, $authorizationId, Request $request)
    {
        $studentId = $request->query->get("student");
        $authorization = (new AuthorizationFacade($this->getDoctrine()->getManager()))->find($authorizationId);
        $student = (new StudentFacade($this->getDoctrine()->getManager()))->find($studentId);
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($parentId);
        $authorizationAttachment = count($authorization->getAttachments()) == 0 ? null : $authorization->getAttachments()[0];
        return ResponseFactory::createWebServiceResponse(true, [
            'subject' => $authorization->getSubject(),
            'message' => $authorization->getMessage(),
            'sendingDate' => $authorization->getSendingDate()->format('Y-m-d H:i:s'),
            'limitDate' => $authorization->getLimitDate()->format('Y-m-d H:i:s'),
            'attachmentId' => $authorizationAttachment == null ? null : $authorizationAttachment->getId(),
            'attachmentName' => $authorizationAttachment == null ? null : $authorizationAttachment->getName(),
            'reply' => $this->getReply($parent, $student, $authorization),
            'replyId' => is_null($parent->getAuthorizationReply($student, $authorization)) ? null : ($parent->getAuthorizationReply($student, $authorization)->getId()),
            'studentName' => $student->getName() . ' ' . $student->getSurname(),
            'authorized' => $student->isAuthorizedTo($authorization)
        ]);
    }

    public function getReply($parent, $student, $authorization)
    {
        $reply = $parent->getAuthorizationReply($student, $authorization);
        return is_null($reply) ? null :
            ($reply->getAuthorized() ? true : false);
    }
}