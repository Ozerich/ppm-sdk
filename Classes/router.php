<?php

namespace ppm;

abstract class Router
{
    abstract protected function getPrefix();

    protected function getUrl($page, $use_lang = true, $use_sport = true)
    {
        return 'http://' . ($use_sport ? $this->getPrefix() : 'ppm') . '.powerplaymanager.com/' . ($use_lang ? 'en/' : '') . $page;
    }

    public function getHistoryScouting($p = null)
    {
        return $this->getUrl('scouting-history.html' . ($p ? '?data=' . $p : ''));
    }

    public function getUser($id)
    {
        return $this->getUrl('manager-profile.html?data=' . $id, true, false);
    }

    public function getPlayer($id)
    {
        return $this->getUrl('player.html?data=' . $id);
    }


    public function getStaff($id)
    {
        return $this->getUrl('staff-member.html?data=' . $id);
    }

    public function getCountryProfile()
    {
        return $this->getUrl('country-profile.html');
    }

    public function getNationalTeamRoster()
    {
        return $this->getUrl('national-team-roster.html');
    }

    public function getTeam($id)
    {
        return $this->getUrl('team.html?data=' . $id);
    }

    public function getCalendar($team_id)
    {
        return $this->getUrl('calendar.html?data=' . $team_id);
    }

    public function getGame($game_id)
    {
        return $this->getUrl('match-report.html?data=' . $game_id);
    }


    public function getBid($player_id)
    {
        return $this->getUrl('_action/action_player.php?action=12&id=' . $player_id, false);
    }

    public function getStaffBid($staff_id)
    {
        return $this->getUrl('_action/action_employee.php?action=12&id=' . $staff_id . '&return_page=' . $this->getStaff($staff_id), false);
    }

    public function getTransfers($page = 1, $search_id = null)
    {
        return $this->getUrl('player-market.html' . ($search_id ? '?data=' . $page . '-' . $search_id : ''));
    }

    public function getStaffTransfers($page = 1, $search_id = null)
    {
        return $this->getUrl('staff-market.html' . ($search_id ? '?data=' . $page . '-' . $search_id : ''));
    }

    public function getFastGames($page = 'rivals')
    {
        return $this->getUrl('instant-challenges.html?data=' . $page);
    }

    public function playFastGame($team_id)
    {
        return $this->getUrl('_action/action_team.php?action=instantChallenge&id_team=' . $team_id, false);
    }

    public function scoutPlayer($player_id)
    {
        return $this->getUrl('_action/action_player.php?action=60&id=' . $player_id, false);
    }

    public function scoutingPage()
    {
        return $this->getUrl('scouting.html');
    }
}


class HockeyRouter extends Router
{
    public function getPrefix()
    {
        return 'hockey';
    }

    public function getGame($game_id)
    {
        return $this->getUrl('game-summary.html?data=' . $game_id);
    }

    public function getTransfers($page = 1, $search_id = null)
    {
        return $this->getUrl('market.html' . ($search_id ? '?data=' . $page . '-' . $search_id : ''));
    }
}

class SoccerRouter extends Router
{
    public function getPrefix()
    {
        return 'soccer';
    }
}

class HandballRouter extends Router
{
    public function getPrefix()
    {
        return 'handball';
    }

    public function getPlayer($id)
    {
        return $this->getUrl('player-profile.html?data=' . $id);
    }

    public function getBid($player_id)
    {
        return $this->getUrl('_action/action_person.php?action=add_market_person_offer&id=' . $player_id . '&type=player', false);
    }

    public function getStaffBid($staff_id)
    {
        return $this->getUrl('_action/action_person.php?action=add_market_person_offer&type=employee&id=' . $staff_id . '&return_page=' . $this->getStaff($staff_id), false);
    }

    public function scoutPlayer($player_id)
    {
        return $this->getUrl('_action/action_player.php?action=scouting_player&id=' . $player_id, false);
    }
}

class BasketballRouter extends Router
{
    public function getPrefix()
    {
        return 'basketball';
    }

    public function getPlayer($id)
    {
        return $this->getUrl('player-profile.html?data=' . $id);
    }

    public function getStaff($id)
    {
        return $this->getUrl('employee.html?data=' . $id);
    }

    public function getBid($player_id)
    {
        return $this->getUrl('_action/action_person.php?action=add_market_person_offer&id=' . $player_id . '&type=player', false);
    }
}