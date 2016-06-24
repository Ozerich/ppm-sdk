<?php

namespace ppm;

class TransferRange
{
    public $from;
    public $to;

    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }


    public function getRange($prefix)
    {
        $result = [];

        if ($this->from && is_numeric($this->from)) {
            $result[$prefix . '_from'] = (int)$this->from;
        }

        if ($this->to && is_numeric($this->to)) {
            $result[$prefix . '_to'] = (int)$this->to;
        }

        return $result;
    }
}

class TransferSkillRange extends TransferRange
{
    public $q_from;
    public $q_to;

    public function __construct($from = null, $to = null, $q_from = null, $q_to = null)
    {
        $this->q_from = $q_from;
        $this->q_to;

        return parent::__construct($from, $to);
    }

    public function getRange($prefix)
    {
        $result = [];

        if ($this->q_from && is_numeric($this->q_from)) {
            $result['qua_' . $prefix . '_from'] = (int)$this->q_from;
        }

        if ($this->q_to && is_numeric($this->q_to)) {
            $result['qua_' . $prefix . '_to'] = (int)$this->q_to;
        }

        return array_merge(parent::getRange($prefix), $result);
    }
}


class BaseTransferCondition
{
    /** @var TransferRange */
    public $age;

    /** @var TransferRange */
    public $price;

}


class TransferCondition extends BaseTransferCondition
{
    const TYPE_ALL = 0;
    const TYPE_TRANSFERS = 1;
    const TYPE_SACKED = 2;
    const TYPE_HCA = 3;
    const TYPE_OFFERS = 4;

    /** @var integer */
    public $type;

    /** @var TransferRange */
    public $career;

    /** @var TransferRange */
    public $experience;

    /** @var TransferRange */
    public $por;

    /** @var TransferRange */
    public $pkvs;

    /** @var TransferSkillRange[] */
    public $skills;

    public function __construct()
    {
        $this->skills = [];

        for ($i = 0; $i <= 10; $i++) {
            $this->skills[$i] = new TransferSkillRange();
        }
    }
}

class StaffTransferCondition extends BaseTransferCondition
{
    /** @var TransferRange */
    public $skill_1;

    /** @var TransferRange */
    public $skill_2;

    /** @var integer */
    public $role;
}