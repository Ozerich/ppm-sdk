<?php

namespace ppm;

abstract class Factory
{
    abstract protected function getSport();

    abstract protected function createRouter();

    abstract protected function createPlayer();

    /** @var  Router */
    private $router;


    public static function GetFactory($sport)
    {
        if ($sport == PPM::SPORT_BASKETBALL) {
            return new BasketballFactory();
        } elseif ($sport == PPM::SPORT_HOCKEY) {
            return new HockeyFactory();
        } elseif ($sport == PPM::SPORT_SOCCER) {
            return new SoccerFactory();
        } elseif ($sport == PPM::SPORT_HANDBALL) {
            return new HandballFactory();
        }

        return null;
    }

    /** @return Router */
    public function getRouter()
    {
        if (!$this->router) {
            $this->router = $this->createRouter();
        }
        return $this->router;
    }

    /** @return Player */
    public function getPlayer()
    {
        return $this->createPlayer();
    }

    /** @return Staff */
    public function getStaff()
    {
        return new Staff($this->getSport());
    }
}

class HockeyFactory extends Factory
{
    protected function getSport()
    {
        return PPM::SPORT_HOCKEY;
    }

    protected function createRouter()
    {
        return new HockeyRouter();
    }

    protected function createPlayer()
    {
        return new HockeyPlayer();
    }
}

class SoccerFactory extends Factory
{
    protected function getSport()
    {
        return PPM::SPORT_SOCCER;
    }

    protected function createRouter()
    {
        return new SoccerRouter();
    }

    protected function createPlayer()
    {
        return new SoccerPlayer();
    }
}

class HandballFactory extends Factory
{
    protected function getSport()
    {
        return PPM::SPORT_HANDBALL;
    }

    protected function createRouter()
    {
        return new HandballRouter();
    }

    protected function createPlayer()
    {
        return new HandballPlayer();
    }
}

class BasketballFactory extends Factory
{
    protected function getSport()
    {
        return PPM::SPORT_BASKETBALL;
    }

    protected function createRouter()
    {
        return new BasketballRouter();
    }

    protected function createPlayer()
    {
        return new BasketballPlayer();
    }
}