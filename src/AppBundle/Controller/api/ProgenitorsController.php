<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 14/06/2017
 * Time: 1:42
 */

namespace AppBundle\Controller\api;

use AppBundle\Entity\Progenitor;
use AppBundle\Facade\CentreFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProgenitorsController extends Controller
{
    public function getParentsAction(Request $request)
    {
        $telephone = $request->query->get("telephone");
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->findByTelephone($telephone);
        return ResponseFactory::createWebServiceResponse(true,
            $parent === null ?
                [
                    'smsCode' => '123456',
                    'found' => false
                ]
                :
                ['id' => $parent->getId(),
                    'name' => $parent->getName(),
                    'telephone' => $parent->getTelephone(),
                    'smsCode' => '123456',
                    'found' => true
                ]
        );
    }

    public function postParentsAction(Request $request)
    {
        $name = $request->request->get("name");
        $telephone = $request->request->get("telephone");
        $parent = new Progenitor($name, $telephone);
        (new ProgenitorFacade($this->getDoctrine()->getManager()))->create($parent);
        return ResponseFactory::createWebServiceResponse(true, [
            'id' => $parent->getId(),
            'name' => $parent->getName(),
            'telephone' => $parent->getTelephone()
        ]);
    }

    public function getParentMessagesAction($id, Request $request)
    {
        $type = $request->query->get("type");
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $messages = $parent->getMessagesOfType($type);
        return ResponseFactory::createWebServiceResponse(true, $this->getMessagesArray($messages, $type));
    }

    private function getMessagesArray($messages, $type): array
    {
        $messagesArray = [];
        foreach ($messages as $message) {
            array_push($messagesArray, $type == 'Authorization' ?
                [
                    'id' => $message['message']->getId(),
                    'subject' => $message['message']->getSubject(),
                    'sendingDate' => $message['message']->getSendingDate()->format('Y-m-d H:i:s'),
                    'attachment' => count($message['message']->getAttachments()) > 0 ? true : false,
                    'limitDate' => $message['message']->getlimitDate()->format('Y-m-d H:i:s'),
                    'studentId' => $message['child']->getId()
                ] :
                [
                    'id' => $message->getId(),
                    'subject' => $message->getSubject(),
                    'sendingDate' => $message->getSendingDate()->format('Y-m-d H:i:s'),
                    'attachment' => count($message->getAttachments()) > 0 ? true : false,
                    'limitDate' => ($type == 'Poll') ? $message->getlimitDate()->format('Y-m-d H:i:s') : null,
                ]);
        }
        return $messagesArray;
    }

    public function putParentsAction($id, Request $request)
    {
        $parentFacade = new ProgenitorFacade($this->getDoctrine()->getManager());
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $parent->setName($request->request->get("newName"));
        $parentFacade->edit();
        return ResponseFactory::createWebServiceResponse(true, [
            'name' => $parent->getName()
        ]);
    }

    public function getParentStudentsAction($id)
    {
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $children = $parent->getChildren();
        return ResponseFactory::createWebServiceResponse(true, $this->getChildrenArray($children));
    }

    private function getChildrenArray($children): array
    {
        $childrenArray = [];
        foreach ($children as $child) {
            array_push($childrenArray,
                [
                    'id' => $child->getId(),
                    'name' => $child->getName(),
                    'surname' => $child->getSurname(),
                ]);
        }
        return $childrenArray;
    }

    public function deleteParentStudentAction($id, $childId)
    {
        $studentFacade = new StudentFacade($this->getDoctrine()->getManager());
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $child = (new StudentFacade($this->getDoctrine()->getManager()))->find($childId);
        $child->removeParent($parent);
        $studentFacade->edit();
        return ResponseFactory::createWebServiceResponse(true, [
        ]);
    }

    public function deleteParentCentresAction($id){
        $parentFacade = new ProgenitorFacade($this->getDoctrine()->getManager());
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $parentFacade->clearCentresOf($parent);
        return ResponseFactory::createWebServiceResponse(true, [
            'Centros totales' => $parent->getCentres()->count()
        ]);
    }

    public function postParentsCentresAction($id, $centreId)
    {
        $centreFacade = new CentreFacade($this->getDoctrine()->getManager());
        $centre = (new CentreFacade($this->getDoctrine()->getManager()))->find($centreId);
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->find($id);
        $centre->addParent($parent);
        $centreFacade->edit();


        return ResponseFactory::createWebServiceResponse(true, [
            'parent' => $parent->getName(),
            'centre' => $centre->getName(),
            'Centros totales' => $parent->getCentres()->count()
        ]);
    }
}