<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 27/06/2017
 * Time: 13:33
 */

namespace AppBundle\Controller\api;


use AppBundle\Entity\PollReply;
use AppBundle\Facade\PollOptionFacade;
use AppBundle\Facade\PollReplyFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PollRepliesController extends Controller
{
    public function postPollrepliesAction(Request $request)
    {
        $pollOption = (new PollOptionFacade($this->getDoctrine()->getManager()))->find($request->request->get("pollOptionId"));
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($request->request->get("parentId"));
        $pollReply = new PollReply($pollOption, $parent);
        (new PollReplyFacade($this->getDoctrine()->getManager()))->create($pollReply);
        return ResponseFactory::createWebServiceResponse(true, [
        ]);
    }
}