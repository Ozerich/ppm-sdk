<?php

namespace ppm;

abstract class Person
{
    const MARKET_SELL = 1;
    const MARKET_SACKED = 2;
    const MARKET_HCA = 3;
    const MARKET_OFFER = 4;

    public $id;

    public $team_id;

    public $name;

    public $age;

    public $on_market = false;

    public $sell_price;

    public $deadline_seconds;

    public $is_my_last_bid;

    protected $skills = null;

    public $market_type = self::MARKET_SELL;

    abstract function getSkillsCount();

    abstract function getUrl();


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

    public function getOr()
    {
        $result = 0;

        foreach ($this->skills as $skill) {
            $result += $skill[0];
        }

        return $result;
    }

}