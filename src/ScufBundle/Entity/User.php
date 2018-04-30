<?php

namespace ScufBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="ScufBundle\Repository\UserRepository")
 */
class User implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var int
     *
     * @ORM\Column(name="hours_todo", type="integer")
     */
    private $hoursTodo;

    /**
     * @var int
     *
     * @ORM\Column(name="hours_done", type="integer", nullable=true)
     */
    private $hoursDone;

    /**
     * @var int
     *
     * @ORM\Column(name="hours_planified", type="integer", nullable=true)
     */
    private $hoursPlanified;

    /**
     * @var int
     *
     * @ORM\Column(name="hours_planified_by_me", type="integer", nullable=true)
     */
    private $hoursPlanifiedByMe;

    /**
     * @var int
     *
     * @ORM\Column(name="overtime", type="integer", nullable=true)
     */
    private $overtime;

    /**
     * @var int
     *
     * @ORM\Column(name="role", type="integer")
     */
    private $role;

    /**
     * @ManyToMany(targetEntity="Access", inversedBy="users")
     * @JoinTable(name="users_access")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $access;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="users")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $superior;

    /**
     * @ManyToMany(targetEntity="Event", inversedBy="users", cascade={"remove"})
     * @JoinTable(name="users_events")
     * @ORM\JoinColumn(nullable=true)
     */
    private $event;

    /**
     * @OneToMany(targetEntity="Action", mappedBy="user", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $action;

    public function __construct() {
        $this->access = new ArrayCollection();
        $this->event = new ArrayCollection();
    }

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
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set hoursTodo
     *
     * @param integer $hoursTodo
     *
     * @return User
     */
    public function setHoursTodo($hoursTodo)
    {
        $this->hoursTodo = $hoursTodo;

        return $this;
    }

    /**
     * Get hoursTodo
     *
     * @return int
     */
    public function getHoursTodo()
    {
        return $this->hoursTodo;
    }

    /**
     * Set hoursDone
     *
     * @param integer $hoursDone
     *
     * @return User
     */
    public function setHoursDone($hoursDone)
    {
        $this->hoursDone = $hoursDone;

        return $this;
    }

    /**
     * Get hoursDone
     *
     * @return int
     */
    public function getHoursDone()
    {
        return $this->hoursDone;
    }

    /**
     * Set hoursPlanified
     *
     * @param integer $hoursPlanified
     *
     * @return User
     */
    public function setHoursPlanified($hoursPlanified)
    {
        $this->hoursPlanified = $hoursPlanified;

        return $this;
    }

    /**
     * Get hoursPlanified
     *
     * @return int
     */
    public function getHoursPlanified()
    {
        return $this->hoursPlanified;
    }

    /**
     * Set hoursPlanifiedByMe
     *
     * @param integer $hoursPlanifiedByMe
     *
     * @return User
     */
    public function setHoursPlanifiedByMe($hoursPlanifiedByMe)
    {
        $this->hoursPlanifiedByMe = $hoursPlanifiedByMe;

        return $this;
    }

    /**
     * Get hoursPlanifiedByMe
     *
     * @return int
     */
    public function getHoursPlanifiedByMe()
    {
        return $this->hoursPlanifiedByMe;
    }

    /**
     * Set overtime
     *
     * @param integer $overtime
     *
     * @return User
     */
    public function setOvertime($overtime)
    {
        $this->overtime = $overtime;

        return $this;
    }

    /**
     * Get overtime
     *
     * @return int
     */
    public function getOvertime()
    {
        return $this->overtime;
    }

    /**
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param mixed $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * @return mixed
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * @param mixed $superior
     */
    public function setSuperior($superior)
    {
        $this->superior = $superior;
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

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getRoles()
    {
        return [];
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // Suppression des données sensibles
        $this->plainPassword = null;
    }
}

