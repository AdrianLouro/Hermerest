<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 13/04/2017
 * Time: 21:08
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Poll;
use AppBundle\Entity\PollReply;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pollOption")
 */
class PollOption
{
    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, name="text", nullable=false)
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="pollOptions")
     * @ORM\JoinColumn(name="poll", referencedColumnName="id", nullable=false, onDelete="cascade")
     */
    private $poll;

    /**
     * @ORM\OneToMany(targetEntity="PollReply", mappedBy="pollOption")
     */
    private $replies;

    public function __construct($text = null, Poll $poll = null)
    {
        $this->replies = new ArrayCollection();
        $this->text = $text;
        $this->poll = $poll;
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
     * Set text
     *
     * @param string $text
     *
     * @return PollOption
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set poll
     *
     * @param Poll $poll
     *
     * @return PollOption
     */
    public function setPoll(Poll $poll)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll
     *
     * @return Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Add reply
     *
     * @param PollReply $reply
     *
     * @return PollOption
     */
    public function addReply(PollReply $reply)
    {
        $this->replies[] = $reply;

        return $this;
    }

    /**
     * Remove reply
     *
     * @param PollReply $reply
     */
    public function removeReply(PollReply $reply)
    {
        $this->replies->removeElement($reply);
    }

    /**
     * Get replies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReplies()
    {
        return $this->replies;
    }

    public function isRepliedBy(Progenitor $parent)
    {
        foreach ($this->getReplies() as $pollReply)
            if ($pollReply->getParent() == $parent) return true;

        return false;
    }
}
