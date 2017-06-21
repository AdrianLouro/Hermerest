<?php

namespace AppBundle\Controller\centre;

use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Utils\ResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ParentsController extends Controller
{
    /**
     * @Route("/parents", name="find_parent")
     * @Method("GET")
     */
    public function findParentAction(Request $request)
    {
        $parent = (new ProgenitorFacade($this->getDoctrine()->getManager()))->findByTelephone($request->query->get('telephone'));
        return ResponseFactory::createJsonResponse(true,
            $parent == null || !$this->centreIsRelatedToParent($parent) ?
                ['found' => false] :
                ['found' => true,
                    'id' => $parent->getId(),
                    'telephone' => $parent->getTelephone(),
                    'fullname' => $parent->getName(),
                ]
        );
    }

    private function centreIsRelatedToParent($parent)
    {
        return $this->get('security.token_storage')->getToken()->getUser()->getCentre()->getParents()->contains($parent);
    }
}
