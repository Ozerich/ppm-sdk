<?php

namespace ppm;

class Team
{
    public $id;

    public $name;

    public $country;

    public $league;


    public function getUrl()
    {
        if (empty($this->id)) {
            return '';
        }

        if ($this instanceof SoccerTeam) {
            $router = new SoccerRouter();
        } elseif ($this instanceof HockeyTeam) {
            $router = new HockeyRouter();
        } else {
            $router = new HandballRouter();
        }

        return $router->getTeam($this->id);
    }
}


class SoccerTeam extends Team
{

}

class HockeyTeam extends Team
{

}

class HandballTeam extends Team
{

}

class BasketballTeam extends Team
{

}