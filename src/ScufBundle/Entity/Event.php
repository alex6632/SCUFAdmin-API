<?php

namespace ScufBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="ScufBundle\Repository\EventRepository")
 */
class Event
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="all_day", type="boolean", nullable=true)
     */
    private $allDay;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="background_color", type="string", length=255, nullable=true)
     */
    private $background_color;

    /**
     * @var string
     *
     * @ORM\Column(name="border_color", type="string", length=255, nullable=true)
     */
    private $border_color;

    /**
     * @var int
     *
     * @ORM\Column(name="validation", type="integer")
     */
    private $validation;

    /**
     * @var int
     *
     * @ORM\Column(name="confirm", type="boolean")
     */
    private $confirm;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="partial_start", type="datetime", nullable=true)
     */
    private $partial_start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="partial_end", type="datetime", nullable=true)
     */
    private $partial_end;

    /**
     * @var string
     *
     * @ORM\Column(name="justification", type="string", length=255, nullable=true)
     */
    private $justification;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="events")
     * @ORM\JoinColumn(name="user_id")
     */
    private $user;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set allDay
     *
     * @param boolean $allDay
     *
     * @return Event
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Get allDay
     *
     * @return bool
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return Event
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     *
     * @return Event
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Event
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param mixed $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->background_color;
    }

    /**
     * @param mixed $background_color
     */
    public function setBackgroundColor($background_color)
    {
        $this->background_color = $background_color;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->border_color;
    }

    /**
     * @param string $border_color
     */
    public function setBorderColor($border_color)
    {
        $this->border_color = $border_color;
    }

    /**
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @param mixed $confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }

    /**
     * @return mixed
     */
    public function getPartialStart()
    {
        return $this->partial_start;
    }

    /**
     * @param mixed $partial_start
     */
    public function setPartialStart($partial_start)
    {
        $this->partial_start = $partial_start;
    }

    /**
     * @return \DateTime
     */
    public function getPartialEnd()
    {
        return $this->partial_end;
    }

    /**
     * @param \DateTime $partial_end
     */
    public function setPartialEnd($partial_end)
    {
        $this->partial_end = $partial_end;
    }

    /**
     * @return mixed
     */
    public function getJustification()
    {
        return $this->justification;
    }

    /**
     * @param mixed $justification
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;
    }
}

