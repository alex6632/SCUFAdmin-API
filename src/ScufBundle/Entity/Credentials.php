<?php

namespace ScufBundle\Entity;

class Credentials
{
    protected $login;

    protected $password;


    public function setLogin($login = null)
    {
        $this->login = $login;

        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setPassword($password = null)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
