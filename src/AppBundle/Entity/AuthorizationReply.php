<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 14/04/2017
 * Time: 0:06
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Authorization;
use AppBundle\Entity\Progenitor;
use AppBundle\Entity\Student;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="authorizationReply", uniqueConstraints={
 * @ORM\UniqueConstraint(columns={"parent", "authorization", "student"})
 * })
 */
class AuthorizationReply
{
    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Authorization", inversedBy="replies")
     * @ORM\JoinColumn(name="authorization", referencedColumnName="id", onDelete="cascade")
     */
    private $authorization;

    /**
     * @ORM\ManyToOne(targetEntity="Progenitor", inversedBy="authorizationReplies")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="authorizationReplies")
     * @ORM\JoinColumn(name="student", referencedColumnName="id", onDelete="cascade")
     */
    private $student;

    /**
     * @ORM\Column(type="boolean", name="authorized", nullable=false)
     */
    private $authorized;

    public function __construct(Authorization $authorization = null, Progenitor $parent = null, Student $student = null, $authorized = null)
    {
        $this->authorization = $authorization;
        $this->parent = $parent;
        $this->student = $student;
        $this->authorized = $authorized;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set authorized
     *
     * @param boolean $authorized
     *
     * @return AuthorizationReply
     */
    public function setAuthorized($authorized)
    {
        $this->authorized = $authorized;

        return $this;
    }

    /**
     * Get authorized
     *
     * @return boolean
     */
    public function getAuthorized()
    {
        return $this->authorized;
    }

    /**
     * Set authorization
     *
     * @param Authorization $authorization
     *
     * @return AuthorizationReply
     */
    public function setAuthorization(Authorization $authorization)
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Get authorization
     *
     * @return Authorization
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Set parent
     *
     * @param Progenitor $parent
     *
     * @return AuthorizationReply
     */
    public function setParent(Progenitor $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Progenitor
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set student
     *
     * @param Student $student
     *
     * @return AuthorizationReply
     */
    public function setStudent(Student $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return Student
     */
    public function getStudent()
    {
        return $this->student;
    }
}
