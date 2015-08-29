<?php

namespace ppm;

abstract class Position
{
    abstract function getName();

    abstract function getUsefulSkills();

    abstract function getSkillProportions();

    public static function GetPositions($sport)
    {
        if ($sport == PPM::SPORT_SOCCER) {
            return [
                new SoccerGoalkeeperPosition(),
                new SoccerDefenderWingerPosition(),
                new SoccerDefenderCenterPosition(),
                new SoccerMiddleWingerPosition(),
                new SoccerMiddleCenterPosition(),
                new SoccerForwardWingerPosition(),
                new SoccerForwardCenterPosition()
            ];
        } elseif ($sport == PPM::SPORT_HOCKEY) {
            return [
                new HockeyGoalkeeperPosition(),
                new HockeyDefenderPosition(),
                new HockeyWingerPosition(),
                new HockeyCenterPosition(),
            ];
        } elseif ($sport == PPM::SPORT_HANDBALL) {
            return [];
        } else {
            return [];
        }
    }
}

class SoccerGoalkeeperPosition extends Position
{
    public function getName()
    {
        return 'Вратарь';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_GOALKEEPER, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_GOALKEEPER => 100,
            SoccerPlayer::SKILL_PAS => 30,
            SoccerPlayer::SKILL_TECH => 70,
            SoccerPlayer::SKILL_SPEED => 70,
            SoccerPlayer::SKILL_HEAD => 40,
        ];
    }
}

class SoccerDefenderWingerPosition extends Position
{
    public function getName()
    {
        return 'Крайний защитник';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_DEFENCE, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_DEFENCE => 100,
            SoccerPlayer::SKILL_PAS => 55,
            SoccerPlayer::SKILL_TECH => 50,
            SoccerPlayer::SKILL_SPEED => 75,
            SoccerPlayer::SKILL_HEAD => 30,
        ];
    }
}

class SoccerDefenderCenterPosition extends Position
{
    public function getName()
    {
        return 'Центральный защитник';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_DEFENCE, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_DEFENCE => 100,
            SoccerPlayer::SKILL_PAS => 55,
            SoccerPlayer::SKILL_TECH => 45,
            SoccerPlayer::SKILL_SPEED => 45,
            SoccerPlayer::SKILL_HEAD => 55,
        ];
    }
}

class SoccerMiddleWingerPosition extends Position
{
    public function getName()
    {
        return 'Крайний полузащитник';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_MIDDLE, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_MIDDLE => 100,
            SoccerPlayer::SKILL_PAS => 60,
            SoccerPlayer::SKILL_TECH => 55,
            SoccerPlayer::SKILL_SPEED => 75,
            SoccerPlayer::SKILL_HEAD => 30,
        ];
    }
}

class SoccerMiddleCenterPosition extends Position
{
    public function getName()
    {
        return 'Центральный полузащитник';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_MIDDLE, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_MIDDLE => 100,
            SoccerPlayer::SKILL_PAS => 75,
            SoccerPlayer::SKILL_TECH => 70,
            SoccerPlayer::SKILL_SPEED => 30,
            SoccerPlayer::SKILL_HEAD => 25,
        ];
    }
}

class SoccerForwardWingerPosition extends Position
{
    public function getName()
    {
        return 'Крайний нападающий';
    }

    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_OFFENCE, SoccerPlayer::SKILL_SHOT, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }


    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_OFFENCE => 100,
            SoccerPlayer::SKILL_SHOT => 65,
            SoccerPlayer::SKILL_PAS => 50,
            SoccerPlayer::SKILL_TECH => 75,
            SoccerPlayer::SKILL_SPEED => 75,
            SoccerPlayer::SKILL_HEAD => 30,
        ];
    }
}

class SoccerForwardCenterPosition extends Position
{
    public function getName()
    {
        return 'Центральный нападающий';
    }


    public function getUsefulSkills()
    {
        return [SoccerPlayer::SKILL_OFFENCE, SoccerPlayer::SKILL_SHOT, SoccerPlayer::SKILL_PAS, SoccerPlayer::SKILL_HEAD, SoccerPlayer::SKILL_SPEED, SoccerPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            SoccerPlayer::SKILL_OFFENCE => 100,
            SoccerPlayer::SKILL_SHOT => 75,
            SoccerPlayer::SKILL_PAS => 30,
            SoccerPlayer::SKILL_TECH => 60,
            SoccerPlayer::SKILL_SPEED => 75,
            SoccerPlayer::SKILL_HEAD => 30,
        ];
    }
}


class HockeyGoalkeeperPosition extends Position
{
    public function getName()
    {
        return 'Вратарь';
    }

    public function getUsefulSkills()
    {
        return [HockeyPlayer::SKILL_GOALKEEPER, HockeyPlayer::SKILL_PAS, HockeyPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            HockeyPlayer::SKILL_GOALKEEPER => 100,
            HockeyPlayer::SKILL_PAS => 50,
            HockeyPlayer::SKILL_TECH => 50
        ];
    }
}

class HockeyDefenderPosition extends Position
{
    public function getName()
    {
        return 'Защитник';
    }

    public function getUsefulSkills()
    {
        return [HockeyPlayer::SKILL_DEFENCE, HockeyPlayer::SKILL_PAS, HockeyPlayer::SKILL_AGR];
    }

    public function getSkillProportions()
    {
        return [
            HockeyPlayer::SKILL_DEFENCE => 100,
            HockeyPlayer::SKILL_PAS => 50,
            HockeyPlayer::SKILL_AGR => 50
        ];
    }
}

class HockeyCenterPosition extends Position
{
    public function getName()
    {
        return 'Центральный нападающий';
    }

    public function getUsefulSkills()
    {
        return [HockeyPlayer::SKILL_OFFENCE, HockeyPlayer::SKILL_SHOT, HockeyPlayer::SKILL_PAS, HockeyPlayer::SKILL_TECH];
    }

    public function getSkillProportions()
    {
        return [
            HockeyPlayer::SKILL_OFFENCE => 100,
            HockeyPlayer::SKILL_SHOT => 70,
            HockeyPlayer::SKILL_PAS => 50,
            HockeyPlayer::SKILL_TECH => 50
        ];
    }
}


class HockeyWingerPosition extends Position
{
    public function getName()
    {
        return 'Крайний нападающий';
    }

    public function getUsefulSkills()
    {
        return [HockeyPlayer::SKILL_OFFENCE, HockeyPlayer::SKILL_SHOT, HockeyPlayer::SKILL_TECH, HockeyPlayer::SKILL_AGR];
    }

    public function getSkillProportions()
    {
        return [
            HockeyPlayer::SKILL_OFFENCE => 100,
            HockeyPlayer::SKILL_SHOT => 80,
            HockeyPlayer::SKILL_TECH => 50,
            HockeyPlayer::SKILL_AGR => 50
        ];
    }
}


class HandballGoalkeeperPosition extends Position
{
    public function getName()
    {
        return 'Вратарь';
    }

    public function getUsefulSkills()
    {
        return [HandballPlayer::SKILL_GOALKEEPER, HandballPlayer::SKILL_BLOCK, HandballPlayer::SKILL_PAS, HandballPlayer::SKILL_TECH, HandballPlayer::SKILL_SPEED];
    }

    public function getSkillProportions()
    {
        return [
            HandballPlayer::SKILL_GOALKEEPER => 100,
            HandballPlayer::SKILL_BLOCK => 80,
            HandballPlayer::SKILL_PAS => 50,
            HandballPlayer::SKILL_TECH => 25,
            HandballPlayer::SKILL_SPEED => 25
        ];
    }
}

class HandballFieldPosition extends Position
{
    public function getName()
    {
        return 'Полевой';
    }

    public function getUsefulSkills()
    {
        return [HandballPlayer::SKILL_FIELD,HandballPlayer::SKILL_SHOT, HandballPlayer::SKILL_BLOCK, HandballPlayer::SKILL_PAS, HandballPlayer::SKILL_TECH, HandballPlayer::SKILL_SPEED, HandballPlayer::SKILL_AGR];
    }

    public function getSkillProportions()
    {
        return [
            HandballPlayer::SKILL_FIELD => 1,
            HandballPlayer::SKILL_SHOT => 1,
            HandballPlayer::SKILL_BLOCK => 1,
            HandballPlayer::SKILL_PAS => 1,
            HandballPlayer::SKILL_TECH => 1,
            HandballPlayer::SKILL_SPEED => 1,
            HandballPlayer::SKILL_AGR => 1
        ];
    }
}


class BasketballPosition extends Position
{
    public function getName()
    {
        return 'Баскетболист';
    }

    public function getUsefulSkills()
    {
        return [];
    }


    public function getSkillProportions(){
        return [];
    }
}