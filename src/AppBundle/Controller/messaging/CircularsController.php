<?php

namespace AppBundle\Controller\messaging;

use AppBundle\Entity\Circular;
use AppBundle\Facade\CircularFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\AttachmentManager;
use AppBundle\Utils\ResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class CircularsController extends Controller
{
    /**
     * @Route("/messaging/circulars", name="circulars")
     */
    public function circularsAction()
    {
        return $this->render('/messaging/circulars.html.twig');
    }

    /**
     * @Route("/circulars", name="send_circular")
     * @Method("POST")
     */
    public function sendCircularAction(Request $request)
    {
        $circularFacade = new CircularFacade($this->getDoctrine()->getManager());
        $sendingDate = new DateTime();
        date_timezone_set($sendingDate, timezone_open('Atlantic/Canary'));

        $circular = new Circular(
            $request->request->get('subject'),
            $request->request->get('message'),
            $sendingDate,
            $this->get('security.token_storage')->getToken()->getUser()->getCentre()
        );
        $circularFacade->create($circular);

        if ($request->request->get('fileName') != null)
            AttachmentManager::attachFileToMessage(
                $request->request->get('fileName'),
                $request->request->get('fileContent'),
                $circular,
                $this->getDoctrine()->getManager());

        $this->sendCircular($request->request->get('studentsIds'), $circular, $circularFacade);
        return ResponseFactory::createJsonResponse(true, [
            'id' => $circular->getId(),
            'subject' => $circular->getSubject(),
        ]);
    }

    /**
     * @Route("/circulars/{id}", name="get_circular")
     * @Method("GET")
     */
    public function getCircularAction(Request $request, $id)
    {
        $circular = (new CircularFacade($this->getDoctrine()->getManager()))->find($id);
        $circularAttachment = count($circular->getAttachments()) == 0 ? null : $circular->getAttachments()[0];
        return ResponseFactory::createJsonResponse(true, [
            'subject' => $circular->getSubject(),
            'message' => $circular->getMessage(),
            'sendingDate' => $circular->getSendingDate(),
            'attachmentId' => $circularAttachment == null ? null : $circularAttachment->getId(),
            'attachmentName' => $circularAttachment == null ? null : $circularAttachment->getName()
        ]);
    }

    private function sendCircular($studentsIds, $circular, $circularFacade)
    {
        foreach ($studentsIds as $studentId) {
            $circular->addStudent((new StudentFacade($this->getDoctrine()->getManager()))->find($studentId));
            $circularFacade->edit();
        }
    }
}
