<?php

namespace AppBundle\Controller\centre;

use AppBundle\Facade\CourseFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\ResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ClassController extends Controller
{
    /**
     * @Route("/centre/class", name="class")
     */
    public function classAction(Request $request)
    {
        $classId = $request->query->get('id');
        $courseFacade = new CourseFacade($this->getDoctrine()->getManager());

        return $this->render('/centre/class.html.twig',
            [
                'class' => $courseFacade->find($classId)
            ]);
    }

    /**
     * @Route("/centre/class/edit", name="edit_class")
     * @Method("POST")
     */
    public function editClassAction(Request $request)
    {
        $className = $request->request->get('className');
        $classId = $request->request->get('classId');
        $courseFacade = new CourseFacade($this->getDoctrine()->getManager());

        $class = $courseFacade->find($classId);
        $centre = $class->getCentre();

        if ($centre->containsClassNamedBy($className) && ($className != $class->getName()))
            return ResponseFactory::createJsonResponse(false, "Ya existe una clase con nombre: " . $className);

        $class->setName($className);
        $courseFacade->edit();

        return ResponseFactory::createJsonResponse(true, [
            'name' => $class->getName()
        ]);
    }

    /**
     * @Route("/centre/class/deleteStudent", name="delete_student_from_class")
     * @Method("POST")
     */
    public function deleteStudentAction(Request $request)
    {
        $studentId = $request->request->get('studentId');
        $studentFacade = new StudentFacade($this->getDoctrine()->getManager());

        $student = $studentFacade->find($studentId);
        $student->setClass(null);
        $studentFacade->edit();

        return ResponseFactory::createJsonResponse(true, [
            'id' => $student->getId(),
            'name' => $student->getName(),
            'surname' => $student->getSurname(),
        ]);
    }

    /**
     * @Route("/centre/class/addStudent", name="add_student_to_class")
     * @Method("POST")
     */
    public function addStudentAction(Request $request)
    {
        $studentId = $request->request->get('studentId');
        $classId = $request->request->get('classId');

        $studentFacade = new StudentFacade($this->getDoctrine()->getManager());
        $courseFacade = new CourseFacade($this->getDoctrine()->getManager());

        $student = $studentFacade->find($studentId);
        $class = $courseFacade->find($classId);

        $oldClasName = $student->getClass() == null ? null : $student->getClass()->getName();

        $student->setClass($class);
        $studentFacade->edit();

        return ResponseFactory::createJsonResponse(true, [
            'id' => $student->getId(),
            'name' => $student->getName(),
            'surname' => $student->getSurname(),
            'oldClassName' => $oldClasName,
        ]);
    }

    /**
     * @Route("/centre/class/delete", name="delete_class")
     * @Method("POST")
     */
    public function deleteClassAction(Request $request)
    {
        $classId = $request->request->get('classId');
        $courseFacade = new CourseFacade($this->getDoctrine()->getManager());

        $class = $courseFacade->find($classId);
        $courseFacade->remove($class);

        return ResponseFactory::createJsonResponse(true, []);
    }

}
