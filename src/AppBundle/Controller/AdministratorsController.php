<?php

namespace AppBundle\Controller;

use AppBundle\Facade\AdministratorFacade;
use AppBundle\Utils\ResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdministratorsController extends Controller
{
    /**
     * @Route("/administrators", name="account")
     */
    public function administratorsAction()
    {
        return $this->render('account.html.twig');
    }

    /**
     * @Route("/administrators/{id}", name="edit_account")
     * @Method("PATCH")
     */
    public function editAccountAction(Request $request, $id)
    {
        $administratorFacade = new AdministratorFacade($this->getDoctrine()->getManager());
        $administrator = $administratorFacade->find($id);

        if ($this->oldPasswordIsIncorrect($request, $administrator))
            return new JsonResponse(['edited' => false, 'error' => "La contraseña actual no es correcta"]);

        $administrator->setUser($request->request->get('user'));
        $administrator->setName($request->request->get('name'));

        if ($this->newPasswordIsSetAndHasCorrectLength($request))
            $administrator->setPassword(md5($request->request->get('newPassword')));

        $administratorFacade->edit();
        return ResponseFactory::createJsonResponse(true, [
            'user' => $administrator->getUser(),
            'name' => $administrator->getName(),
        ]);
    }

    private function newPasswordIsSetAndHasCorrectLength(Request $request)
    {
        return strlen($request->request->get('newPassword')) >= 4 && strlen($request->request->get('newPassword')) <= 16;
    }

    private function oldPasswordIsIncorrect(Request $request, $administrator)
    {
        return md5($request->request->get('oldPassword')) != $administrator->getPassword();
    }
}
