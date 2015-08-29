<?php

namespace ppm;

abstract class Factory
{
    abstract protected function createRouter();

    abstract protected function createPlayer();

    /** @var  Router */
    private $router;

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
}

class HockeyFactory extends Factory
{
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
    protected function createRouter()
    {
        return new BasketballRouter();
    }

    protected function createPlayer()
    {
        return new BasketballPlayer();
    }
}