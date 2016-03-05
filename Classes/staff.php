<?php

namespace ppm;

class Staff extends Person
{
    const ROLE_COACH = 1;
    const ROLE_PHYSIOTHERAPIST = 2;
    const ROLE_MANAGER = 3;
    const ROLE_STADIUM_GUARD = 4;
    const ROLE_LECTURER = 5;
    const ROLE_SPORTING_DIRECTOR = 6;
    const ROLE_DOCTOR = 7;

    public static function GetRoleLabels()
    {
        return [
            'Coach' => self::ROLE_COACH,
            'Lecturer' => self::ROLE_LECTURER,
            'Manager' => self::ROLE_MANAGER,
            'Physician' => self::ROLE_DOCTOR,
            'Physiotherapist' => self::ROLE_PHYSIOTHERAPIST,
            'Sporting director' => self::ROLE_SPORTING_DIRECTOR,
            'Arena custodian' => self::ROLE_STADIUM_GUARD
        ];
    }


    public $sport;

    public function __construct($sport)
    {
        $this->sport = $sport;
        parent::__construct();
    }

    public $role;

    public function getSkillsCount()
    {
        return 2;
    }

    public function getUrl()
    {
        $factory = Factory::GetFactory($this->sport);

        return $factory->getRouter()->getStaff($this->id);
    }
}