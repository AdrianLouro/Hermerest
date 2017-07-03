<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 20/06/2017
 * Time: 14:20
 */

namespace AppBundle\Controller\api;


use AppBundle\Entity\AuthorizationReply;
use AppBundle\Facade\AuthorizationFacade;
use AppBundle\Facade\AuthorizationReplyFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationRepliesController extends Controller
{
    public function putAuthorizationrepliesAction($id, Request $request)
    {
        $authorizationReply = (new AuthorizationReplyFacade($this->getDoctrine()->getManager()))->find($id);
        $authorizationReply->setAuthorized($request->request->get("authorized"));
        (new AuthorizationReplyFacade($this->getDoctrine()->getManager()))->edit();
        $authorization = (new AuthorizationFacade($this->getDoctrine()->getManager()))->find($request->request->get("authorizationId"));
        $student = (new StudentFacade($this->getDoctrine()->getManager()))->find($request->request->get("studentId"));
        return ResponseFactory::createWebServiceResponse(true, [
            'authorized' => $student->isAuthorizedTo($authorization),
            'reply' => $request->request->get("authorized"),
            'replyId' => $authorizationReply->getId()
        ]);
    }

    public function postAuthorizationrepliesAction(Request $request)
    {
        $authorization = (new AuthorizationFacade($this->getDoctrine()->getManager()))->find($request->request->get("authorizationId"));
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($request->request->get("parentId"));
        $student = (new StudentFacade($this->getDoctrine()->getManager()))->find($request->request->get("studentId"));
        $authorized = $request->request->get("authorized");
        $authorizationReply = new AuthorizationReply($authorization,$parent,$student,$authorized);
        (new AuthorizationReplyFacade($this->getDoctrine()->getManager()))->create($authorizationReply);
        return ResponseFactory::createWebServiceResponse(true, [
            'authorized' => $student->isAuthorizedTo($authorization),
            'reply' => $authorized,
            'replyId' => $authorizationReply->getId()
        ]);
    }
}