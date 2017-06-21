<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Course;
use AppBundle\Entity\Message;
use AppBundle\Entity\Progenitor;
use AppBundle\Entity\Student;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="centre")
 */
class Centre
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
     * @ORM\OneToMany(targetEntity="Course", mappedBy="centre")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $classes;

    /**
     * @ORM\OneToMany(targetEntity="Student", mappedBy="centre")
     * @ORM\OrderBy({"surname" = "ASC", "name" = "ASC"})
     */
    private $students;

    /**
     * @ORM\ManyToMany(targetEntity="Progenitor", inversedBy="centres")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinTable(name="centre_parent",
     *      joinColumns={@ORM\JoinColumn(name="centre", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")}
     *      )
     */
    private $parents;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="centre")
     * @ORM\OrderBy({"sendingDate" = "DESC"})
     */
    private $messages;

    public function __construct($name = null)
    {
        $this->classes = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->name = $name;
    }

    public function containsClassNamedBy($name)
    {
        foreach ($this->getClasses() as $class)
            if (strtolower($class->getName()) == strtolower($name)) return true;
        return false;
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
     * @return Centre
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
     * Add class
     *
     * @param Course $class
     *
     * @return Centre
     */
    public function addClass(Course $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    /**
     * Remove class
     *
     * @param Course $class
     */
    public function removeClass(Course $class)
    {
        $this->classes->removeElement($class);
    }

    /**
     * Get classes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Add student
     *
     * @param Student $student
     *
     * @return Centre
     */
    public function addStudent(Student $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param Student $student
     */
    public function removeStudent(Student $student)
    {
        $this->students->removeElement($student);
    }

    /**
     * Get students
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * Add message
     *
     * @param Message $message
     *
     * @return Centre
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

    /**
     * Add parent
     *
     * @param Progenitor $parent
     *
     * @return Centre
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
}
