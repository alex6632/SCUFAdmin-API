<?php

namespace ScufBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * EventParent
 *
 * @ORM\Table(name="event_parent")
 * @ORM\Entity(repositoryClass="ScufBundle\Repository\EventParentRepository")
 */
class EventParent
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="weekday", type="integer", nullable=true)
     */
    private $weekday;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_time", type="time", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_time", type="time", nullable=true)
     */
    private $endTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="repeats", type="integer", nullable=true)
     */
    private $repeats;

    /**
     * @var int|null
     *
     * @ORM\Column(name="repeat_freq", type="integer", nullable=true)
     */
    private $repeatFreq;

    /**
     * @OneToMany(targetEntity="Event", mappedBy="eventParent", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $event;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set weekday.
     *
     * @param int $weekday
     *
     * @return EventParent
     */
    public function setWeekday($weekday)
    {
        $this->weekday = $weekday;

        return $this;
    }

    /**
     * Get weekday.
     *
     * @return int
     */
    public function getWeekday()
    {
        return $this->weekday;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime|null $startDate
     *
     * @return EventParent
     */
    public function setStartDate($startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set startTime.
     *
     * @param \DateTime|null $startTime
     *
     * @return EventParent
     */
    public function setStartTime($startTime = null)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return \DateTime|null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime.
     *
     * @param \DateTime|null $endTime
     *
     * @return EventParent
     */
    public function setEndTime($endTime = null)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime|null
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set repeats.
     *
     * @param int|null $repeats
     *
     * @return EventParent
     */
    public function setRepeats($repeats = null)
    {
        $this->repeats = $repeats;

        return $this;
    }

    /**
     * Get repeats.
     *
     * @return int|null
     */
    public function getRepeats()
    {
        return $this->repeats;
    }

    /**
     * Set repeatFreq.
     *
     * @param int|null $repeatFreq
     *
     * @return EventParent
     */
    public function setRepeatFreq($repeatFreq = null)
    {
        $this->repeatFreq = $repeatFreq;

        return $this;
    }

    /**
     * Get repeatFreq.
     *
     * @return int|null
     */
    public function getRepeatFreq()
    {
        return $this->repeatFreq;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }
}
