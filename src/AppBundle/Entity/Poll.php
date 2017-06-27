<?php
/**
 * Created by PhpStorm.
 * User: alour
 * Date: 13/04/2017
 * Time: 21:08
 */

namespace AppBundle\Entity;

use AppBundle\Entity\PollOption;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="poll")
 */
class Poll extends Message
{
    /**
     * @ORM\Column(type="datetimetz", name="limitDate", nullable=false)
     */
    private $limitDate;

    /**
     * @ORM\Column(type="boolean", name="multipleChoice", nullable=false)
     */
    private $multipleChoice;

    /**
     * @ORM\OneToMany(targetEntity="PollOption", mappedBy="poll")
     * @ORM\OrderBy({"text" = "ASC"})
     */
    private $pollOptions;

    public function __construct($subject = null, $message = null, ?DateTime $sendingDate = null, ?Centre $centre = null, ?DateTime $limitDate = null, $multipleChoice = null)
    {
        $this->pollOptions = new ArrayCollection();
        $this->limitDate = $limitDate;
        $this->multipleChoice = $multipleChoice;
        parent::__construct($subject, $message, $sendingDate, $centre);
    }

    /**
     * Set limitDate
     *
     * @param \DateTime $limitDate
     *
     * @return Poll
     */
    public function setLimitDate($limitDate)
    {
        $this->limitDate = $limitDate;

        return $this;
    }

    /**
     * Get limitDate
     *
     * @return \DateTime
     */
    public function getLimitDate()
    {
        return $this->limitDate;
    }

    /**
     * Set multipleChoice
     *
     * @param boolean $multipleChoice
     *
     * @return Poll
     */
    public function setMultipleChoice($multipleChoice)
    {
        $this->multipleChoice = $multipleChoice;

        return $this;
    }

    /**
     * Get multipleChoice
     *
     * @return boolean
     */
    public function getMultipleChoice()
    {
        return $this->multipleChoice;
    }

    /**
     * Add pollOption
     *
     * @param PollOption $pollOption
     *
     * @return Poll
     */
    public function addPollOption(PollOption $pollOption)
    {
        $this->pollOptions[] = $pollOption;

        return $this;
    }

    /**
     * Remove pollOption
     *
     * @param PollOption $pollOption
     */
    public function removePollOption(PollOption $pollOption)
    {
        $this->pollOptions->removeElement($pollOption);
    }

    /**
     * Get pollOptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPollOptions()
    {
        return $this->pollOptions;
    }

    public function isRepliedBy(Progenitor $parent)
    {
        foreach ($this->getPollOptions() as $pollOption)
            if ($pollOption->isRepliedBy($parent)) return true;

        return false;
    }
}
