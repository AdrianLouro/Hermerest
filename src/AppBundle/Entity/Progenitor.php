<?php

namespace AppBundle\Entity;

use AppBundle\Entity\AuthorizationReply;
use AppBundle\Entity\Centre;
use AppBundle\Entity\PollReply;
use AppBundle\Entity\Student;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="parent")
 */
class Progenitor
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
     * @ORM\Column(type="string", length=50, name="telephone", nullable=false, unique=true)
     */
    private $telephone;

    /**
     * @ORM\ManyToMany(targetEntity="Student", mappedBy="parents")
     * * @ORM\OrderBy({"surname" = "ASC", "name" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Centre", mappedBy="parents")
     * * @ORM\OrderBy({"name" = "ASC"})
     */
    private $centres;

    /**
     * @ORM\OneToMany(targetEntity="AuthorizationReply", mappedBy="parent")
     */
    private $authorizationReplies;

    /**
     * @ORM\OneToMany(targetEntity="PollReply", mappedBy="parent")
     */
    private $pollReplies;

    public function __construct($name = null, $telephone = null)
    {
        $this->children = new ArrayCollection();
        $this->centres = new ArrayCollection();
        $this->authorizationReplies = new ArrayCollection();
        $this->pollReplies = new ArrayCollection();
        $this->name = $name;
        $this->telephone = $telephone;
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
     * @return Progenitor
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
     * Set telephone
     *
     * @param string $telephone
     *
     * @return Progenitor
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Add child
     *
     * @param Student $child
     *
     * @return Progenitor
     */
    public function addChild(Student $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Student $child
     */
    public function removeChild(Student $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add authorizationReply
     *
     * @param AuthorizationReply $authorizationReply
     *
     * @return Progenitor
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
     * Add pollReply
     *
     * @param PollReply $pollReply
     *
     * @return Progenitor
     */
    public function addPollReply(PollReply $pollReply)
    {
        $this->pollReplies[] = $pollReply;

        return $this;
    }

    /**
     * Remove pollReply
     *
     * @param PollReply $pollReply
     */
    public function removePollReply(PollReply $pollReply)
    {
        $this->pollReplies->removeElement($pollReply);
    }

    /**
     * Get pollReplies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPollReplies()
    {
        return $this->pollReplies;
    }

    /**
     * Get authorizationReply
     *
     * @return AuthorizationReply
     */
    public function getAuthorizationReply(Student $student, Authorization $authorization)
    {
        foreach ($this->authorizationReplies as $authorizationReply)
            if ($authorizationReply->getStudent()->getId() == $student->getId() &&
                $authorizationReply->getAuthorization()->getId() == $authorization->getId())
                    return $authorizationReply;

        return null;
    }

    /**
     * Add centre
     *
     * @param Centre $centre
     *
     * @return Progenitor
     */
    public function addCentre(Centre $centre)
    {
        $this->centres[] = $centre;

        return $this;
    }

    /**
     * Remove centre
     *
     * @param Centre $centre
     */
    public function removeCentre(Centre $centre)
    {
        $this->centres->removeElement($centre);
    }

    /**
     * Get centres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCentres()
    {
        return $this->centres;
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
        foreach ($this->children as $child)
            $this->addChildMessagesToChildrenMessages($child->getMessagesOfType($type), $messages, $type, $child);

        return $messages;
    }

    private function addChildMessagesToChildrenMessages($childMessages, $childrenMessages, $type, $child)
    {
        foreach ($childMessages as $childMessage) {
            if ($type != "Authorization" && $childrenMessages->contains($childMessage)) continue;
            $childrenMessages->add($type == "Authorization" ?
                ['message' => $childMessage, 'child' => $child] :
                $childMessage
            );
        }
    }
}
