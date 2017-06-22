<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 14/04/2017
 * Time: 0:06
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Authorization;
use AppBundle\Entity\PollOption;
use AppBundle\Entity\Progenitor;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pollReply", uniqueConstraints={
 * @ORM\UniqueConstraint(columns={"parent", "pollOption"})
 * })
 */
class PollReply
{
    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PollOption", inversedBy="replies")
     * @ORM\JoinColumn(name="pollOption", referencedColumnName="id", onDelete="cascade")
     */
    private $pollOption;

    /**
     * @ORM\ManyToOne(targetEntity="Progenitor", inversedBy="pollReplies")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")
     */
    private $parent;

    public function __construct(PollOption $pollOption = null, Progenitor $parent = null)
    {
        $this->pollOption = $pollOption;
        $this->parent = $parent;
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
     * Set parent
     *
     * @param Progenitor $parent
     *
     * @return PollReply
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
     * Set pollOption
     *
     * @param PollOption $pollOption
     *
     * @return PollReply
     */
    public function setPollOption(PollOption $pollOption)
    {
        $this->pollOption = $pollOption;

        return $this;
    }

    /**
     * Get pollOption
     *
     * @return PollOption
     */
    public function getPollOption()
    {
        return $this->pollOption;
    }
}
