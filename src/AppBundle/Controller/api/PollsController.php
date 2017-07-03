<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 20/06/2017
 * Time: 14:38
 */

namespace AppBundle\Controller\api;


use AppBundle\Facade\PollFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PollsController extends Controller
{
    public function getPollsAction(Request $request, $id)
    {
        $poll = (new PollFacade($this->getDoctrine()->getManager()))->find($id);
        $pollAttachment = count($poll->getAttachments()) == 0 ? null : $poll->getAttachments()[0];
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($request->query->get("parent"));
        return ResponseFactory::createWebServiceResponse(true, [
            'subject' => $poll->getSubject(),
            'message' => $poll->getMessage(),
            'sendingDate' => $poll->getSendingDate()->format('Y-m-d H:i:s'),
            'limitDate' => $poll->getLimitDate()->format('Y-m-d H:i:s'),
            'options' => $this->getPollResults($poll),
            'attachmentId' => $pollAttachment == null ? null : $pollAttachment->getId(),
            'attachmentName' => $pollAttachment == null ? null : $pollAttachment->getName(),
            'multiple' => $poll->getMultipleChoice(),
            'replied' => $poll->isRepliedBy($parent)
        ]);
    }

    private function getPollResults($poll): array
    {
        $pollResults = array();
        foreach ($poll->getPollOptions() as $pollOption)
            array_push($pollResults, [
                'name' => $pollOption->getText(),
                'id' => $pollOption->getId()
            ]);
        return $pollResults;
    }
}