<?php

namespace ppm;

abstract class Player
{
    const SIDE_LEFT = 'l';
    const SIDE_RIGHT = 'r';
    const SIDE_UNIVERSAL = 'u';

    abstract function getSkillsCount();

    /** @return Position */
    abstract public function getPosition();

    abstract function getSkillLabels();

    public $id;

    public $name;

    public $experience;

    public $prefer_side;


    public $on_market = false;

    public $sell_price;

    public $deadline_seconds;

    public $is_my_last_bid;


    /** @var Team */
    public $team;

    public function createTeam()
    {
        if ($this instanceof SoccerPlayer) {
            $this->team = new SoccerTeam();
        } elseif ($this instanceof HockeyPlayer) {
            $this->team = new HockeyTeam();
        } elseif ($this instanceof HandballPlayer) {
            $this->team = new HandballTeam();
        } elseif($this instanceof BasketballPlayer) {
            $this->team = new BasketballTeam();
        }

        return $this->team;
    }

    public $national_team = false;

    public $national_team_work = 0;

    public $age;

    public $career;

    private $scouted = false;

    private $skills = null;

    /** @var  Position */
    private $position;

    public function __construct()
    {
        $skills_count = $this->getSkillsCount();

        for ($i = 0; $i < $skills_count; $i++) {
            $this->skills[] = [null, null];
        }
    }

    public function setSkill($skill_id, $skill_value, $skill_quality)
    {
        if (!isset($this->skills[$skill_id])) {
            throw new \Exception("Incorrect skill_id");
        }

        $this->skills[$skill_id] = [(int)$skill_value, (int)$skill_quality];

        if ($this->position) {
            $this->position = null;
        }

        $this->scouted = true;
    }

    public function getSkill($skill_id)
    {
        return isset($this->skills[$skill_id]) ? $this->skills[$skill_id][0] : null;
    }

    public function getSkillQuality($skill_id)
    {
        return isset($this->skills[$skill_id]) ? $this->skills[$skill_id][1] : null;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function isScouted()
    {
        return $this->scouted;
    }


    public function getUrl()
    {
        if ($this instanceof SoccerPlayer) {
            $router = new SoccerRouter();
        } elseif ($this instanceof HockeyPlayer) {
            $router = new HockeyRouter();
        } elseif ($this instanceof HandballPlayer) {
            $router = new HandballRouter();
        } else {
            $router = null;
        }

        return $router->getPlayer($this->id);
    }


    public function getOr()
    {
        $result = 0;

        foreach ($this->skills as $skill) {
            $result += $skill[0];
        }

        return $result;
    }

    public function getUsefulOr($calc_exp = false)
    {
        $position = $this->getPosition();
        if (!$position) {
            return 0;
        }

        $result = 0;

        $usefull_skills = $position->getUsefulSkills();
        foreach ($usefull_skills as $skill_id) {
            $result += $this->getSkill($skill_id);
        }

        if ($calc_exp) {
            $p = $this->experience * 0.2;
            $result = $result * (1 + $p / 100);
        }

        return floor($result);
    }

    public function getUsefulOrInNational()
    {
        $result = $this->getUsefulOr(true);

        $team_percent = $this->national_team_work * 0.2;
        $result = $result * (1 + $team_percent / 100);

        return floor($result);
    }


    public function getUsefulQuality()
    {
        $position = $this->getPosition();
        if (!$position) {
            return 0;
        }

        $proportion = $position->getSkillProportions();

        $sum = 0;
        foreach ($proportion as $v) {
            $sum += $v;
        }

        $result = 0;
        foreach ($proportion as $skill_id => $v) {
            $q = $this->getSkillQuality($skill_id);
            $result += $q * ($v / $sum);
        }

        return floor($result);
    }
}

class SoccerPlayer extends Player
{
    const SKILL_GOALKEEPER = 0;
    const SKILL_DEFENCE = 1;
    const SKILL_MIDDLE = 2;
    const SKILL_OFFENCE = 3;
    const SKILL_SHOT = 4;
    const SKILL_PAS = 5;
    const SKILL_TECH = 6;
    const SKILL_SPEED = 7;
    const SKILL_HEAD = 8;

    public function getSkillsCount()
    {
        return 9;
    }

    public function getSkillLabels()
    {
        return [
            'goalie', 'defense', 'midfield', 'attack', 'shooting', 'passing', 'technique', 'speed', 'heading',
        ];
    }

    public function getPosition()
    {
        if ($this->isScouted() == false) {
            return null;
        }

        if ($this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_DEFENCE) &&
            $this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_MIDDLE) &&
            $this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_OFFENCE)
        ) {
            return new SoccerGoalkeeperPosition();
        }

        if ($this->getSkill(self::SKILL_DEFENCE) > $this->getSkill(self::SKILL_MIDDLE)
            && $this->getSkill(self::SKILL_DEFENCE) > $this->getSkill(self::SKILL_OFFENCE)
        ) {

            return ($this->getSkill(self::SKILL_SPEED) / $this->getSkill(self::SKILL_PAS)) > 1.1 ?
                new SoccerDefenderWingerPosition() : new SoccerDefenderCenterPosition();
        }

        if ($this->getSkill(self::SKILL_MIDDLE) > $this->getSkill(self::SKILL_OFFENCE)) {

            return $this->getSkill(self::SKILL_PAS) > $this->getSkill(self::SKILL_SPEED) ?
                new SoccerMiddleCenterPosition() : new SoccerMiddleWingerPosition();
        }

        return ($this->getSkill(self::SKILL_SPEED) / $this->getSkill(self::SKILL_TECH)) <= 1.1 ?
            new SoccerForwardWingerPosition() : new SoccerForwardCenterPosition();
    }
}

class HockeyPlayer extends Player
{
    const SKILL_GOALKEEPER = 0;
    const SKILL_DEFENCE = 1;
    const SKILL_OFFENCE = 2;
    const SKILL_SHOT = 3;
    const SKILL_PAS = 4;
    const SKILL_TECH = 5;
    const SKILL_AGR = 6;


    public function getSkillsCount()
    {
        return 7;
    }

    public function getSkillLabels()
    {
        return [
            'goalie', 'defense', 'attack', 'shooting', 'passing', 'technics', 'aggressive'
        ];
    }

    public function getPosition()
    {
        if ($this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_DEFENCE) &&
            $this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_OFFENCE)
        ) {
            return new HockeyGoalkeeperPosition();
        }

        if ($this->getSkill(self::SKILL_DEFENCE) > $this->getSkill(self::SKILL_OFFENCE)) {
            return new HockeyDefenderPosition();
        }

        return $this->getSkill(self::SKILL_PAS) > $this->getSkill(self::SKILL_AGR) ?
            new HockeyCenterPosition() : new HockeyWingerPosition();
    }
}

class HandballPlayer extends Player
{
    const SKILL_GOALKEEPER = 0;
    const SKILL_FIELD = 1;
    const SKILL_SHOT = 2;
    const SKILL_BLOCK = 3;
    const SKILL_PAS = 4;
    const SKILL_TECH = 5;
    const SKILL_SPEED = 6;
    const SKILL_AGR = 7;

    public function getSkillsCount()
    {
        return 8;
    }

    public function getSkillLabels()
    {
        return [
            'goalie', 'field_play', 'shooting', 'block', 'passing', 'technique', 'speed', 'aggressivity'
        ];
    }

    public function getPosition()
    {
        if ($this->getSkill(self::SKILL_GOALKEEPER) > $this->getSkill(self::SKILL_FIELD)) {
            return new HandballGoalkeeperPosition();
        } else {
            return new HandballFieldPosition();
        }
    }


    public function getUsefulOr($calc_exp = false)
    {
        $position = $this->getPosition();
        if (!$position) {
            return 0;
        }

        $result = 0;

        $usefull_skills = $position->getUsefulSkills();

        foreach ($usefull_skills as $skill_id) {
            if ($position instanceof HandballGoalkeeperPosition || ($skill_id != self::SKILL_BLOCK && $skill_id != self::SKILL_SHOT)) {
                $result += $this->getSkill($skill_id);
            }
        }

        if ($position instanceof HandballGoalkeeperPosition == false) {
            $result += max($this->getSkill(self::SKILL_BLOCK), $this->getSkill(self::SKILL_SHOT));
        }

        if ($calc_exp) {
            $p = $this->experience * 0.2;
            $result = $result * (1 + $p / 100);
        }

        return floor($result);
    }
}

class BasketballPlayer extends Player
{
    public function getSkillsCount()
    {
        return 7;
    }

    public function getSkillLabels()
    {
        return [
            '1', '2', '3', '4', '5', '6', '7'
        ];
    }


    public function getPosition()
    {
        return new BasketballPosition();
    }
}