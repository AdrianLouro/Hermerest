<?php

namespace AppBundle\Controller\messaging;

use AppBundle\Entity\Poll;
use AppBundle\Entity\PollOption;
use AppBundle\Facade\PollFacade;
use AppBundle\Facade\PollOptionFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\AttachmentManager;
use AppBundle\Utils\ResponseFactory;
use DateTime;
use DateTimeZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PollsController extends Controller
{
    /**
     * @Route("/messaging/polls", name="polls")
     */
    public function pollsAction()
    {
        return $this->render('/messaging/polls.html.twig');
    }

    /**
     * @Route("/polls", name="send_poll")
     * @Method("POST")
     */
    public function sendPollAction(Request $request)
    {
        $pollFacade = new PollFacade($this->getDoctrine()->getManager());
        $sendingDate = new DateTime();
        date_timezone_set($sendingDate, timezone_open('Atlantic/Canary'));
        $poll = new Poll(
            $request->request->get('subject'),
            $request->request->get('message'),
            $sendingDate,
            $this->get('security.token_storage')->getToken()->getUser()->getCentre(),
            date_create_from_format('Y-m-d G:i:s', $request->request->get('limitDate') . " 23:59:59", new DateTimeZone('UTC')),
            $request->request->get('multipleChoice') == "true" ? true : false
        );
        $pollFacade->create($poll);
        $this->addPollOptionsToPoll($request->request->get('options'), $poll);

        if ($request->request->get('fileName') != null)
            AttachmentManager::attachFileToMessage(
                $request->request->get('fileName'),
                $request->request->get('fileContent'),
                $poll, $this->getDoctrine()->getManager()
            );

        $this->sendPoll($request->request->get('studentsIds'), $poll, $pollFacade);
        return ResponseFactory::createJsonResponse(true, [
            'id' => $poll->getId(),
            'limitDate' => $poll->getLimitDate(),
            'subject' => $poll->getSubject(),
        ]);
    }

    /**
     * @Route("/polls/{id}", name="get_poll")
     * @Method("GET")
     */
    public function getPollAction(Request $request, $id)
    {
        $poll = (new PollFacade($this->getDoctrine()->getManager()))->find($id);
        $pollAttachment = count($poll->getAttachments()) == 0 ? null : $poll->getAttachments()[0];
        return ResponseFactory::createJsonResponse(true, [
            'subject' => $poll->getSubject(),
            'message' => $poll->getMessage(),
            'sendingDate' => $poll->getSendingDate(),
            'limitDate' => $poll->getLimitDate(),
            'options' => $this->getPollResults($poll),
            'attachmentId' => $pollAttachment == null ? null : $pollAttachment->getId(),
            'attachmentName' => $pollAttachment == null ? null : $pollAttachment->getName()
        ]);
    }

    private function addPollOptionsToPoll($pollOptions, $poll)
    {
        foreach ($pollOptions as $pollOptionText)
            (new PollOptionFacade($this->getDoctrine()->getManager()))->create(new PollOption($pollOptionText, $poll));
    }

    private function sendPoll($studentsIds, $poll, $pollFacade)
    {
        foreach ($studentsIds as $studentId) {
            $poll->addStudent((new StudentFacade($this->getDoctrine()->getManager()))->find($studentId));
            $pollFacade->edit();
        }
    }

    private function getPollResults($poll): array
    {
        $pollResults = array();
        foreach ($poll->getPollOptions() as $pollOption)
            array_push($pollResults, [$pollOption->getText(), count($pollOption->getReplies())]);

        return $pollResults;
    }
}
