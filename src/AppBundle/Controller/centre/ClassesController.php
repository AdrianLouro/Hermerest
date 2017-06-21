<?php

namespace AppBundle\Controller\centre;

use AppBundle\Entity\Course;
use AppBundle\Entity\Student;
use AppBundle\Facade\CentreFacade;
use AppBundle\Facade\CourseFacade;
use AppBundle\Facade\ProgenitorFacade;
use AppBundle\Facade\StudentFacade;
use AppBundle\Utils\ResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ClassesController extends Controller
{
    /**
     * @Route("/centre/classes", name="classes")
     */
    public function classesAction()
    {
        return $this->render('/centre/classes.html.twig', ['classes' => $this->getUser()->getCentre()->getClasses()]);
    }

    /**
     * @Route("/classes", name="add_class")
     * @Method("POST")
     */
    public function addClassAction(Request $request)
    {
        $centre = (new CentreFacade($this->getDoctrine()->getManager()))->find($request->request->get('centreId'));

        if ($centre->containsClassNamedBy($request->request->get('className')))
            return ResponseFactory::createJsonResponse(false, "Ya existe una clase con nombre: " . $request->request->get('className'));

        $newClass = new Course($request->request->get('className'), $centre);
        (new CourseFacade($this->getDoctrine()->getManager()))->create($newClass);
        return ResponseFactory::createJsonResponse(true, [
            'id' => $newClass->getId(),
            'name' => $newClass->getName()
        ]);
    }

    /**
     * @Route("/classes/autoimport", name="autoimport_class")
     * @Method("POST")
     */
    public function autoimportClassAction(Request $request)
    {
        $courseFacade = new CourseFacade($this->getDoctrine()->getManager());
        $studentFacade = new StudentFacade($this->getDoctrine()->getManager());
        $parentFacade = new ProgenitorFacade($this->getDoctrine()->getManager());
        $className = $request->request->get('className');
        $centre = $this->get('security.token_storage')->getToken()->getUser()->getCentre();
        $students = $request->request->get('students');
        array_pop($students);

        if ($centre->containsClassNamedBy($className))
            return ResponseFactory::createJsonResponse(false, "Ya existe una clase con nombre: " . $className);

        $class = new Course($className, $centre);
        $courseFacade->create($class);

        $this->autoimportStudentsToClass($students, $class, $centre, $studentFacade, $courseFacade, $parentFacade);

        return ResponseFactory::createJsonResponse(true, [
            'id' => $class->getId(),
            'name' => $class->getName(),
            'students' => count($class->getStudents()),
        ]);
    }

    private function autoimportStudentsToClass($students, $class, $centre, $studentFacade, $courseFacade, $parentFacade)
    {
        foreach ($students as $student) {
            $studentFields = explode(',', $student);
            $studentName = $studentFields[0];
            $studentSurname = $studentFields[1];

            $student = new Student($studentName, $studentSurname, $class, $centre);
            $studentFacade->create($student);
            $class->addStudent($student);
            $courseFacade->edit();

            array_shift($studentFields);
            array_shift($studentFields);
            $this->autoimportParentsToStudent($studentFacade, $parentFacade, $studentFields, $student);
        }
    }

    private function autoimportParentsToStudent($studentFacade, $parentFacade, $studentFields, $student)
    {
        foreach ($studentFields as $parentTelephone) {
            $parent = $parentFacade->findByTelephone($parentTelephone);
            if ($parent == null || !$this->centreIsRelatedToParent($parent)) continue;
            $student->addParent($parent);
            $studentFacade->edit();
        }
    }

    private function centreIsRelatedToParent($parent)
    {
        return $this->get('security.token_storage')->getToken()->getUser()->getCentre()->getParents()->contains($parent);
    }

}
