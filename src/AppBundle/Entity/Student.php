<?php

namespace AppBundle\Entity;

use AppBundle\Entity\AuthorizationReply;
use AppBundle\Entity\Centre;
use AppBundle\Entity\Course;
use AppBundle\Entity\Message;
use AppBundle\Entity\Progenitor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="student")
 */
class Student
{
    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=50, name="name", nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, name="surname", nullable=false)
     */
    private $surname;

    /**
     * @ORM\ManyToOne(targetEntity="Course", inversedBy="students")
     * @ORM\JoinColumn(name="class", referencedColumnName="id", onDelete="set null")
     */
    private $class;

    /**
     * @ORM\ManyToOne(targetEntity="Centre", inversedBy="students")
     * @ORM\JoinColumn(name="centre", referencedColumnName="id", nullable=false, onDelete="cascade")
     */
    private $centre;

    /**
     * @ORM\ManyToMany(targetEntity="Progenitor", inversedBy="children")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinTable(name="student_parent",
     *      joinColumns={@ORM\JoinColumn(name="student", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")}
     *      )
     */
    private $parents;

    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="students")
     * @ORM\OrderBy({"sendingDate" = "DESC"})
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="AuthorizationReply", mappedBy="student")
     */
    private $authorizationReplies;

    public function __construct($name = null, $surname = null, Course $class = null, Centre $centre = null)
    {
        $this->parents = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->authorizationReplies = new ArrayCollection();
        $this->name = $name;
        $this->surname = $surname;
        $this->class = $class;
        $this->centre = $centre;
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
     * Set name
     *
     * @param string $name
     *
     * @return Student
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return Student
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set class
     *
     * @param Course $class
     *
     * @return Student
     */
    public function setClass(Course $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return Course
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set centre
     *
     * @param Centre $centre
     *
     * @return Student
     */
    public function setCentre(Centre $centre)
    {
        $this->centre = $centre;

        return $this;
    }

    /**
     * Get centre
     *
     * @return Centre
     */
    public function getCentre()
    {
        return $this->centre;
    }

    /**
     * Add parent
     *
     * @param Progenitor $parent
     *
     * @return Student
     */
    public function addParent(Progenitor $parent)
    {
        $this->parents[] = $parent;

        return $this;
    }

    /**
     * Remove parent
     *
     * @param Progenitor $parent
     */
    public function removeParent(Progenitor $parent)
    {
        $this->parents->removeElement($parent);
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Add message
     *
     * @param Message $message
     *
     * @return Student
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add authorizationReply
     *
     * @param AuthorizationReply $authorizationReply
     *
     * @return Student
     */
    public function addAuthorizationReply(AuthorizationReply $authorizationReply)
    {
        $this->authorizationReplies[] = $authorizationReply;

        return $this;
    }

    /**
     * Remove authorizationReply
     *
     * @param AuthorizationReply $authorizationReply
     */
    public function removeAuthorizationReply(AuthorizationReply $authorizationReply)
    {
        $this->authorizationReplies->removeElement($authorizationReply);
    }

    /**
     * Get authorizationReplies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthorizationReplies()
    {
        return $this->authorizationReplies;
    }

    /**
     * Get messages by type
     *
     * @param $type
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessagesOfType($type)
    {
        $messages = new ArrayCollection();
        $className = "\\AppBundle\\Entity\\" . $type;
        $class = get_class(new $className());

        foreach ($this->messages as $message)
            if ($message instanceof $class)
                $messages->add($message);

        return $messages;
    }


    public function isAuthorizedTo(Authorization $authorization)
    {
        $replies = $authorization->getReplies();
        $yes = 0;
        $no = 0;
        $this->getYesAndNoForAuthorization($replies, $yes, $no);

        if (($yes == 0 && $no == 0) || $no > 0) return false;
        else return true;
    }

    private function getYesAndNoForAuthorization($replies, &$yes, &$no)
    {
        foreach ($replies as $reply) {
            if ($reply->getStudent() === $this && $reply->getAuthorized()) $yes++;
            if ($reply->getStudent() === $this && !$reply->getAuthorized()) $no++;
        }
    }
}
