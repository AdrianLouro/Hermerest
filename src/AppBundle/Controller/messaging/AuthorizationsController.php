<?php

namespace AppBundle\Controller\messaging;

use AppBundle\Entity\Authorization;
use AppBundle\Facade\AuthorizationFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\AttachmentManager;
use AppBundle\Utils\ResponseFactory;
use DateTime;
use DateTimeZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationsController extends Controller
{
    /**
     * @Route("/messaging/authorizations", name="authorizations")
     */
    public function authorizationssAction()
    {
        return $this->render('/messaging/authorizations.html.twig');
    }

    /**
     * @Route("/authorizations", name="send_authorization")
     * @Method("POST")
     */
    public function sendAuthorizationAction(Request $request)
    {
        $authorizationFacade = new AuthorizationFacade($this->getDoctrine()->getManager());
        $sendingDate = new DateTime();
        date_timezone_set($sendingDate, timezone_open('Atlantic/Canary'));
        $authorization = new Authorization(
            $request->request->get('subject'),
            $request->request->get('message'),
            $sendingDate,
            $this->get('security.token_storage')->getToken()->getUser()->getCentre(),
            date_create_from_format('Y-m-d G:i:s', $request->request->get('limitDate') . " 23:59:59", new DateTimeZone('UTC'))
        );
        $authorizationFacade->create($authorization);

        if ($request->request->get('fileName') != null)
            AttachmentManager::attachFileToMessage(
                $request->request->get('fileName'),
                $request->request->get('fileContent'),
                $authorization,
                $this->getDoctrine()->getManager()
            );

        $this->sendAuthorization($request->request->get('studentsIds'), $authorization, $authorizationFacade);
        return ResponseFactory::createJsonResponse(true, [
            'id' => $authorization->getId(),
            'limitDate' => $authorization->getLimitDate(),
            'subject' => $authorization->getSubject(),
        ]);
    }

    /**
     * @Route("/authorizations/{id}", name="edit_authorization_limit_date")
     * @Method("PATCH")
     */
    public function editLimitDateAction(Request $request, $id)
    {
        $authorization = (new AuthorizationFacade($this->getDoctrine()->getManager()))->find($id);
        $authorization->setLimitDate(
            date_create_from_format('Y-m-d G:i:s', $request->request->get('limitDate') . " 23:59:59", new DateTimeZone('UTC'))
        );
        (new AuthorizationFacade($this->getDoctrine()->getManager()))->edit();

        return ResponseFactory::createJsonResponse(true, [
            'id' => $authorization->getId(),
            'limitDate' => $authorization->getLimitDate(),
        ]);
    }

    /**
     * @Route("/authorizations/{id}", name="get_authorization")
     * @Method("GET")
     */
    public function getAuthorizationAction(Request $request, $id)
    {
        $authorization = (new AuthorizationFacade($this->getDoctrine()->getManager()))->find($id);
        $authorizationAttachment = count($authorization->getAttachments()) == 0 ? null : $authorization->getAttachments()[0];
        return ResponseFactory::createJsonResponse(true, [
            'subject' => $authorization->getSubject(),
            'message' => $authorization->getMessage(),
            'sendingDate' => $authorization->getSendingDate(),
            'limitDate' => $authorization->getLimitDate(),
            'students' => $this->getAuthorizationResults($authorization),
            'attachmentId' => $authorizationAttachment == null ? null : $authorizationAttachment->getId(),
            'attachmentName' => $authorizationAttachment == null ? null : $authorizationAttachment->getName()
        ]);
    }

    private function sendAuthorization($studentsIds, $authorization, $authorizationFacade)
    {
        foreach ($studentsIds as $studentId) {
            $authorization->addStudent((new StudentFacade($this->getDoctrine()->getManager()))->find($studentId));
            $authorizationFacade->edit();
        }
    }

    private function getAuthorizationResults($authorization)
    {
        $students = array();
        foreach ($authorization->getStudents() as $student)
            array_push($students,
                [
                    $student->getSurname() . ", " . $student->getName(),
                    $student->isAuthorizedTo($authorization) ? 1 : 0
                ]);
        return $students;
    }
}
