<?php

namespace ppm;

require_once "Classes/logger.php";
require_once "Classes/downloader.php";
require_once "Classes/router.php";
require_once "Classes/position.php";
require_once "Classes/team.php";
require_once "Classes/person.php";
require_once "Classes/user.php";
require_once "Classes/player.php";
require_once "Classes/staff.php";
require_once "Classes/factory.php";
require_once "Classes/transfer_condition.php";

class PPM
{
    const SPORT_HOCKEY = 1;
    const SPORT_SOCCER = 2;
    const SPORT_HANDBALL = 3;
    const SPORT_BASKETBALL = 4;

    public static function GetAllSports()
    {
        return [
            self::SPORT_HOCKEY,
            self::SPORT_SOCCER,
            self::SPORT_HANDBALL,
            self::SPORT_BASKETBALL,
        ];
    }

    private $sport = null;

    /** @var Factory */
    private $factory;

    /** @var Downloader */
    private $downloader;

    /** @var Logger */
    private $logger;


    private $login;
    private $password;

    private $authed = false;

    public function __construct()
    {
        set_time_limit(0);

        $this->downloader = new Downloader();

        $this->setLogger(new Logger());
    }

    public function setCredentials($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    public function getDownloader()
    {
        return $this->downloader;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function disableLog()
    {
        $this->logger = new NullLogger();
    }

    public function auth($login, $password)
    {
        $this->login = $login;
        $this->password = $password;

        return $this->reauth();
    }

    private function reauth()
    {
        $this->logger->write('AUTH');

        $response = $this->downloader->post('http://www.powerplaymanager.com/action/action_ams_user_login.php', [
            'lng' => 'en',
            'username' => $this->login,
            'password' => $this->password
        ], true);

        if (strpos($response, 'Login to PPM')) {
            $this->authed = false;
            $this->logger->write('FAIL');
            return false;
        }

        $this->logger->write('OK');
        $this->authed = true;
        return true;
    }

    public function selectSport($sport)
    {
        if ($sport == 'soccer' || $sport == 'football') {
            $sport = self::SPORT_SOCCER;
        } elseif ($sport == 'hockey') {
            $sport = self::SPORT_HOCKEY;
        } elseif ($sport == 'handball') {
            $sport = self::SPORT_HANDBALL;
        } elseif ($sport == 'basketball') {
            $sport = self::SPORT_BASKETBALL;
        }

        $this->sport = $sport;

        $this->factory = Factory::GetFactory($this->sport);
    }


    public function getPlayerPositions()
    {
        return Position::GetPositions($this->sport);
    }

    public function createPlayer()
    {
        return $this->factory->getPlayer();
    }

    private $nt_coach = null;

    private function isCoachOfNationalTeam($team_id)
    {
        if ($this->nt_coach === null) {
            $this->nt_coach = [];

            $page_url = $this->factory->getRouter()->getCountryProfile();

            $page = $this->downloader->get($page_url);

            if (preg_match_all("#<tr class='border_top border_bottom'>.+?data=(\d+).*?<td class='tr1td2 name'>(.+?)</td>.+?<td class='tr0td2 name'>(.+?)</td>#si", $page, $nt, PREG_SET_ORDER)) {
                foreach ($nt as $nt_item) {
                    $item_team_id = trim($nt_item[1]);
                    $manager1 = trim(strip_tags($nt_item[2]));
                    $manager2 = trim(strip_tags($nt_item[3]));

                    if ($manager1 == 'Ozerich' || $manager2 == 'Ozerich') {
                        $this->nt_coach[] = $item_team_id;
                    }
                }
            }
        }

        return in_array($team_id, $this->nt_coach);
    }

    private $national_team_teamwork_data = null;

    private function getNationalTeamWork($player_id)
    {
        if ($this->national_team_teamwork_data === null) {
            $this->national_team_teamwork_data = [];

            $page_url = $this->factory->getRouter()->getNationalTeamRoster();
            $page = $this->downloader->get($page_url);

            if (preg_match_all("#<tr>.+?<a href.+?data=(\d+).+?<td>\&nbsp;(\d+)</td>#si", $page, $list, PREG_SET_ORDER)) {
                foreach ($list as $item) {
                    $this->national_team_teamwork_data[$item[1]] = $item[2];
                }
            }

        }

        return isset($this->national_team_teamwork_data[$player_id]) ? $this->national_team_teamwork_data[$player_id] : false;
    }

    public function getUser($user_id)
    {
        $page_url = $this->factory->getRouter()->getUser($user_id);
        $page = $this->downloader->get($page_url);

        preg_match_all("#<table class='table_profile'(.+?)</table>#si", $page, $tables, PREG_SET_ORDER);
        $table = $tables[1][1];

        preg_match('#<td.+?>(.+?)</td>#si', $table, $data);
        $last_login = $data[1];

        $user = new User;

        $user->id = $user_id;
        $user->last_login = $last_login;

        return $user;
    }

    public function getTeam($team_id)
    {
        if (!$this->authed) {
            $this->reauth();
        }

        $page_url = $this->factory->getRouter()->getTeam($team_id);
        $page = $this->downloader->get($page_url);

        if (!preg_match_all('#h1_add_info.+?>(.+?)</div>#si', $page, $items, PREG_SET_ORDER)) {
            return null;
        }
        if (!preg_match('#\?data\=(\d+)\-#si', $items[2][1], $user_id)) {
            return null;
        }
        $user_id = $user_id[1];

        $model = new Team;

        $model->user_id = $user_id;

        return $model;
    }

    /** @return Player */
    public function getPlayer($player_id)
    {
        if (!$this->authed) {
            $this->reauth();
        }

        $page_url = $this->factory->getRouter()->getPlayer($player_id);
        $page = $this->downloader->get($page_url);

        if (!preg_match("#<div class='top_info_menu'(.+?)</div>#si", $page, $login) || strpos($login[1], $this->login) !== false) {
            $this->reauth();
            $page = $this->downloader->get($page_url);
        }

        $model = $this->factory->getPlayer();

        $model->id = $player_id;
        if (preg_match("#<title>(.+?)- Player profile#si", $page, $name)) {
            $model->name = trim($name[1]);
        }

        if (preg_match("#id='age'>(.+?)</td>#si", $page, $age)) {
            $model->age = $age[1];
        }

        if (preg_match("#id='life_time'>.+?(\d)#si", $page, $career)) {
            $model->career = $career[1];
        }

        if (preg_match("#id='table-1'.+?<tbody>(.+?)</table>#si", $page, $skills)) {
            $skills_data = $skills[1];
            if (preg_match_all('#<span.*?>(.+?)</span>.*?<span.*?>(.+?)</span>#si', $skills_data, $skills, PREG_SET_ORDER)) {
                foreach ($skills as $ind => $skill) {
                    if (strpos($skill[1], '(') !== false || $skill[1] == 'OR') {
                        break;
                    }
                    $model->setSkill($ind, strip_tags($skill[1]), strip_tags($skill[2]));
                }
            }
        }

        if (preg_match("#id='experience'>(.+?)</td>#si", $page, $exp)) {
            $model->experience = (int)$exp[1];
        }

        if (preg_match("#id='prs'>(.+?)</td>#si", $page, $prs)) {
            if ($prs[1] == 'Left') {
                $model->prefer_side = Player::SIDE_LEFT;
            } elseif ($prs[1] == 'Right') {
                $model->prefer_side = Player::SIDE_RIGHT;
            } else {
                $model->prefer_side = Player::SIDE_UNIVERSAL;
            }
        }

        if (preg_match('#This player is on the.+?data=(profile-)*(\d+).+?Belarus(.+?)<#si', $page, $nt)) {
            $model->national_team = true;

            if ($this->isCoachOfNationalTeam($nt[2])) {
                $model->national_team_work = $this->getNationalTeamWork($model->id);
            } else {
                $model->national_team_work = false;
            }
        }

        $model->is_scouted_queue = strpos($page, 'The player is placed in the queue for scouting.') !== false;

        if (preg_match("#<div class='player_info'>.+?<a.*?data=(.+?)'.*?<a.*?>.+?</a>.*?<a.*?data=(\d+).*?>(.+?)</a>.*?<a.*?>(.+?)</a>.*?#si", $page, $team_preg)) {
            $team = $model->createTeam();

            $team->country = $team_preg[1];
            $team->id = $team_preg[2];
            $team->name = $team_preg[3];
            $team->league = $team_preg[4];

            $model->team = $team;
        }


        if (strpos($page, 'The player is on the market') !== false && strpos($page, 'The bidding deadline has passed.') == false) {
            $model->on_market = true;

            preg_match('#table_profile(.+?)</table>#si', $page, $table);
            preg_match_all("#<td class='tr\dtd2.+?>(.+?)</td>#si", $table[1], $cells);

            preg_match("#value='(\d+)'#si", $cells[1][1], $seconds);
            $model->deadline_seconds = $seconds[1];

            if (isset($cells[1][2])) {
                $model->sell_price = (int)str_replace(',', '', $cells[1][2]);
            }

            if (strpos($page, 'disabled') !== false) {
                $model->is_my_last_bid = true;
            }
        }

        if (preg_match("#<a class='link_name'(.+?)>#si", $page, $preg)) {
            if (preg_match('#data\=(\d+)#si', $preg[1], $preg)) {
                $model->team_id = $preg[1];
            }
        }

        return $model;
    }

    /** @return Player */
    public function getStaff($staff_id)
    {
        $page_url = $this->factory->getRouter()->getStaff($staff_id);
        $page = $this->downloader->get($page_url);

        if (!preg_match("#<div class='top_info_menu'(.+?)</div>#si", $page, $login) || strpos($login[1], $this->login) !== false) {
            $this->reauth();
            $page = $this->downloader->get($page_url);
        }

        $model = $this->factory->getStaff();

        $model->id = $staff_id;
        if (preg_match("#<title>(.+?)- Staff#si", $page, $name)) {
            $model->name = trim($name[1]);
        }

        if (preg_match("#<strong>Age</strong>.+?<td.*?>(.+?)</td>#si", $page, $age)) {
            $model->age = $age[1];
        }

        $role_labels = Staff::GetRoleLabels();

        if (preg_match("#<div class='h1_add_info'>.+?<div class='h1_add_info'>(.+?)</div>#si", $page, $role)) {
            $role = $role[1];
            if (isset($role_labels[$role])) {
                $model->role = $role_labels[$role];
            }
        }

        if (preg_match('#Miscellaneous info(.+?)</table>#si', $page, $skills_text)) {
            $skills_text = $skills_text[1];
            if (preg_match_all('#<td.*?>(.+?)</td>#si', $skills_text, $skills)) {
                $skills = $skills[1];
                $model->setSkill(0, $skills[1], strip_tags($skills[2]));
                $model->setSkill(1, $skills[7], strip_tags($skills[8]));
            }
        }


        if (strpos($page, 'The staff member is on the market') !== false && strpos($page, 'The bidding deadline has passed.') == false) {
            $model->on_market = true;

            preg_match('#table_profile(.+?)</table>#si', $page, $table);
            preg_match_all("#<td class='tr\dtd2.+?>(.+?)</td>#si", $table[1], $cells);

            preg_match("#value='(\d+)'#si", $cells[1][1], $seconds);
            $model->deadline_seconds = $seconds[1];

            if (isset($cells[1][2])) {
                $model->sell_price = (int)str_replace(',', '', $cells[1][2]);
            }

            if (strpos($page, 'disabled') !== false) {
                $model->is_my_last_bid = true;
            }
        }

        return $model;
    }

    /** @return Player[] */
    public function getScoutedPlayers()
    {
        $this->logger->write('Start search scouted players');

        $result = array();

        $page_url = $this->factory->getRouter()->getHistoryScouting();

        $page = $this->downloader->get($page_url);

        if (preg_match('#page 1 of (\d+)#si', $page, $pages_count)) {
            $pages_count = (int)$pages_count[1];
        } else {
            $pages_count = 1;
        }

        $this->logger->write('Found ' . $pages_count . ' pages');

        for ($p = 1; $p <= $pages_count; $p++) {

            $this->logger->write('Page ' . $p);

            if ($p > 1) {
                $page_url = $this->factory->getRouter()->getHistoryScouting($p);
                $page = $this->downloader->get($page_url);
            }

            if (preg_match('#<table(.+?)</table>#si', $page, $table)) {
                if (preg_match_all('#data=(\d+).*?</td>.*?<td.*?>.*?</td>.*?<td.*?>(.+?)</td>#si', $table[1], $players, PREG_SET_ORDER)) {
                    foreach ($players as $player) {
                        if ($player[2] == 'Player') {
                            $player = $this->getPlayer($player[1]);
                            $result[] = $player;
                        };
                    }
                }
            }
        }

        return $result;
    }


    private $_player_ratings = [];
    private $_player_rating_teams = [];

    public function getPlayerRating(SoccerPlayer $player)
    {
        if (!$player->team) {
            throw new \Exception("Cannot get rating for player without team");
        }

        /*
         if (isset($this->_player_ratings[$player->id])) {
             return $this->_player_ratings[$player->id];
         } elseif (isset($this->_player_rating_teams[$player->team->id])) {
             return null;
         }
        */

        $calendar_url = $this->factory->getRouter()->getCalendar($player->team->id);
        $page = $this->downloader->get($calendar_url);

        $result = [];
        if (preg_match_all("#<div class='calendary_day'.*?><div class='cal_number'>(\d+).*?cal_teamname(.+?)</div></div>#si", $page, $days_preg, PREG_SET_ORDER)) {
            $days_preg = array_reverse($days_preg);

            foreach ($days_preg as $day_item) {
                $day = $day_item[1];
                if (preg_match("#href='.*data=(\d+)'#si", $day_item[2], $report_id)) {
                    $game_id = $report_id[1];

                    $game_url = $this->factory->getRouter()->getGame($game_id);
                    $page = $this->downloader->get($game_url);

                    if (preg_match("#id='stats'.+?<tbody>(.+?)</tbody>.*?<tbody>(.+?)</tbody>#si", $page, $data)) {
                        $data = $data[1] . $data[2];
                        if (preg_match_all("#<tr>\s*<td class='tr.td1'>(.{0,2}?)</td>.{0,500}?data=(\d+).*?(\d+)</td>\s+</tr>#si", $data, $players, PREG_SET_ORDER)) {
                            foreach ($players as $player_data) {
                                $result[$player_data[2]] = [
                                    'url' => $game_url,
                                    'date' => date('Y') . '-' . date('m') . '-' . $day,
                                    'position' => $player_data[1],
                                    'rating' => $player_data[3],
                                ];
                            }
                        }
                    }

                    if (isset($result[$player->id]) && $result[$player->id]['rating']) {

                        if (empty($result[$player->id]['position'])) {
                            if (preg_match("#" . $player->id . ".{0,50}?'>.+?</a>\s\((.+?)\)#si", $page, $position_preg)) {
                                $result[$player->id]['position'] = $position_preg[1];
                            }
                        }

                        break;
                    }
                }
            }
        }

        foreach ($result as $player_id => $player_data) {
            $this->_player_ratings[$player_id] = $player_data;
        }

        return isset($this->_player_ratings[$player->id]) && $this->_player_ratings[$player->id]['rating'] > 0 ? $this->_player_ratings[$player->id] : null;
    }


    public function bid(Person $player, $amount)
    {
        if ($player instanceof Player) {
            $url = $this->factory->getRouter()->getBid($player->id);
            $player_url = $this->factory->getRouter()->getPlayer($player->id);
        } elseif ($player instanceof Staff) {
            $url = $this->factory->getRouter()->getStaffBid($player->id);
            $player_url = $this->factory->getRouter()->getStaff($player->id);
        } else {
            throw new \InvalidArgumentException();
        }

        $response = $this->downloader->post($url, [
            'send' => '',
            'xprice' => $amount,
            'price' => $amount,
            'return_page' => $player_url,
        ]);

        $bid_label = null;
        foreach (['id_item', 'id_transfer'] as $bid_id_label) {
            if (preg_match("#name='" . $bid_id_label . "' value='(\d+?)'#si", $response, $id)) {
                $bid_id = $id[1];
                $bid_label = $bid_id_label;
                break;
            }
        }

        if ($bid_label && $bid_id) {
            $this->downloader->post($url, [
                'check_yes' => '',
                $bid_label => $bid_id,
                'price' => $amount,
                'return_page' => $player_url,
            ]);
        }

        return true;
    }


    public function autoBid(Person $player, $amount, $finishCallback)
    {
        $this->logger->write('Start autobid');

        while (true) {
            $person = $player instanceof Player ? $this->getPlayer($player->id) : $this->getStaff($player->id);

            if ($person->on_market == false) {
                $this->logger->write('Person is no sold');

                if ($finishCallback) {
                    call_user_func($finishCallback, $person->is_my_last_bid);
                }

                break;
            }

            $this->logger->write($person->deadline_seconds . ' -> ' . $person->sell_price);

            if ($person->is_my_last_bid) {
                $this->logger->write('MY BID');
                sleep($person->deadline_seconds > 70 ? $person->deadline_seconds - 70 : 5);
                continue;
            }

            if ($person->is_my_last_bid == false && ($person->deadline_seconds <= 5 || ($person->deadline_seconds >= 61 && $person->deadline_seconds <= 63))) {

                $bid_delta = max(10000, ceil($person->sell_price / 100 * 5));
                $new_bid = $person->sell_price + $bid_delta;

                if ($new_bid > $amount) {
                    $this->logger->write("Next bid is more than limit: FAIL");
                    if ($finishCallback) {
                        call_user_func($finishCallback, false);
                    }
                    break;
                }

                $this->logger->write('Bid: ' . $new_bid);

                $this->bid($person, $new_bid);
                sleep(59);
            } else {
                $waiting_seconds = $person->deadline_seconds > 60 ? $person->deadline_seconds - 63 : $person->deadline_seconds - 3;
                $waiting_seconds = max($waiting_seconds, 0);
                $this->logger->write('Waiting: ' . $waiting_seconds);
                sleep($waiting_seconds);
            }
        }
    }


    /** @return Player[] */
    public function getTransfers(TransferCondition $condition, $options = [])
    {
        $params = [
            'action' => 'save_filter',
            'submit' => '',
            'name_filter' => '',
            'country' => 'first',
            'market_type' => $condition->type,
        ];

        if ($condition->price && $condition->price->to) {
			if($condition->type == TransferCondition::TYPE_TRANSFERS){
				$params['price_to'] = (int)$condition->price->to;
			}
        }

        if ($condition->por) {
            $params['index_skill_from'] = $condition->por->from + 50;
        }

        if ($condition->age) {
            $params = array_merge($params, $condition->age->getRange('age'));
        }

        if ($condition->career) {
            $params = array_merge($params, $condition->career->getRange('life_time'));
        }

        if ($condition->experience) {
            $params = array_merge($params, $condition->experience->getRange('experience'));
        }

        if ($condition->skills) {
            $player = $this->factory->getPlayer();
            $skill_labels = $player->getSkillLabels();

            foreach ($skill_labels as $i => $skill_label) {
                if (isset($condition->skills[$i])) {
                    $params = array_merge($params, $condition->skills[$i]->getRange($skill_label));
                }
            }
        }

        $result = [];
        $search_id = null;
        for ($page = 1; $page <= 50; $page++) {

            $url = $this->factory->getRouter()->getTransfers($page, $search_id);
            $text = $page == 1 ? $this->downloader->post($url, $params) : $this->downloader->get($url);

            preg_match("#<div class='pagination'>.*?data\=\d\-(\d+)#sui", $text, $search_id);
            $search_id = $search_id[1];

            preg_match("#id='table-1'.*?>.+?tbody>(.+?)</tbody>#sui", $text, $text);
            $text = $text[1];
            if (preg_match_all('#<tr.*?>(.+?)</tr>#sui', $text, $rows)) {
                foreach ($rows[1] as $row) {
                    if (preg_match_all('#<td.*?>(.+?)</td>#sui', $row, $cells)) {
                        $cells = $cells[1];

                        $player = $this->factory->getPlayer();
                        $player->on_market = true;

                        $first_cell = $cells[0];

                        preg_match('#<a href.*?>(.+?)</a>#sui', $first_cell, $name);
                        $player->name = $name[1];

                        preg_match('#\?data\=(\d+)#sui', $first_cell, $id);
                        $player->id = $id[1];
						
						if(strpos($row, 'FFE3EA') !== false){
							$player->market_type = Player::MARKET_HCA;
						}
						else{
                            if (preg_match("#value\='(\d+)'#sui", $first_cell, $seconds)) {
                                $player->deadline_seconds = $seconds[1];
                            } elseif (preg_match('#\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d#sui', $first_cell, $time)) {
                                $player->deadline_seconds = strtotime($time[0]) - time();
                            }
						}
						
						if($player->market_type != Player::MARKET_HCA){
                            if (preg_match('#Price: (.+?)$#sui', $first_cell, $price)) {
                                $player->sell_price = preg_replace('#[^\d]+#sui', '', $price[1]);
                            }
						}


                        $player->age = $cells[1];
                        $player->career = (int)strip_tags($cells[($this->sport == self::SPORT_SOCCER ? 3 : 4)]);

                        for ($i = 0; $i < $player->getSkillsCount(); $i++) {
                            $cell = $cells[($this->sport == self::SPORT_SOCCER ? 4 : 5) + $i];
                            if (preg_match('#(\d+)<span.+?>(\d+)#si', $cell, $preg)) {
                                $player->setSkill($i, (int)$preg[1], (int)$preg[2]);
                            }
                        }

                        $player->experience = $cells[$player->getSkillsCount() + ($this->sport == self::SPORT_SOCCER ? 4 : 5)];

                        $player->prefer_side = strtolower($cells[count($cells) - 1]);

                        if ($condition->por) {
                            $player_por = $player->getUsefulOr();

                            if ($condition->por->from && $player_por < $condition->por->from) continue;
                            if ($condition->por->to && $player_por > $condition->por->to) continue;
                        }

                        if ($condition->pkvs) {
                            $player_pkvs = $player->getUsefulQuality();

                            if ($condition->pkvs->from && $player_pkvs < $condition->pkvs->from) continue;
                            if ($condition->pkvs->to && $player_pkvs > $condition->pkvs->to) continue;
                        }

                        $result[] = $player;
                    }
                }
            } else {
                break;
            }
        }
		
		
		if ($condition->price && $condition->price->to && $condition->type != TransferCondition::TYPE_HCA) {
			$price_to = $condition->price->to;
			$result = array_filter($result, function(Player $player) use ($price_to){
				if ($player->market_type == Person::MARKET_HCA) {
                    return true;
                }
				return $player->sell_price <= $price_to;
			});
		}

        if (isset($options['days_inactive'])) {
            $days_inactive = (int)$options['days_inactive'];
            if ($days_inactive > 0) {
                $result = array_filter($result, function (Player $player) use ($days_inactive) {
                    if ($player->market_type != Person::MARKET_HCA) {
                        return true;
                    }

                    $player = $this->getPlayer($player->id);
                    $team = $this->getTeam($player->team_id);

                    if ($team) {
                        $user = $this->getUser($team->user_id);
                        return $user->getDaysInActive() >= $days_inactive;
                    } else {
                        return true;
                    }
                });
            }
        }

        return $result;
    }


    /** @return Staff[] */
    public function getStaffTransfers(StaffTransferCondition $condition)
    {
        $params = [
            'country' => 'first',
            'type' => $condition->role,
        ];

        foreach (['price', 'age', 'contract', 'salary', 'totally_quality', 'index_skill', 'attrib1', 'attrib2', 'qua_attrib1', 'qua_attrib2'] as $param) {
            $params[$param . '_from'] = '';
            $params[$param . '_to'] = '';
        }

        if ($condition->price) {
            $params = array_merge($params, $condition->price->getRange('price'));
        }
        if ($condition->age) {
            $params = array_merge($params, $condition->age->getRange('age'));
        }
        if ($condition->skill_1) {
            $params = array_merge($params, $condition->skill_1->getRange($this->sport == self::SPORT_HANDBALL ? 'attribute_1' : 'attrib1'));
        }
        if ($condition->skill_2) {
            $params = array_merge($params, $condition->skill_2->getRange($this->sport == self::SPORT_HANDBALL ? 'attribute_2' : 'attrib2'));
        }

        $params = array_merge($params, [
            'action' => 'save_filter',
            'submit' => '',
            'name_filter' => ''
        ]);

        $result = [];
        $search_id = null;
        for ($page = 1; $page <= 50; $page++) {
            $url = $this->factory->getRouter()->getStaffTransfers($page, $search_id);

            $text = $page == 1 ? $this->downloader->post($url, $params) : $this->downloader->get($url);

            preg_match("#<div class='pagination'>.*?data\=\d\-(\d+)#sui", $text, $search_id);
            $search_id = $search_id[1];

            preg_match("#id='table-1'.*?>.+?tbody>(.+?)</tbody>#sui", $text, $text);
            $text = $text[1];
            if (preg_match_all('#<tr.*?>(.+?)</tr>#sui', $text, $rows)) {
                foreach ($rows[1] as $row) {
                    if (preg_match_all('#<td.*?>(.+?)</td>#sui', $row, $cells)) {
                        $cells = $cells[1];

                        $model = $this->factory->getStaff();

                        if (preg_match("#value\='(\d+)'#sui", $cells[0], $seconds)) {
                            $model->deadline_seconds = $seconds[1];
                        } elseif (preg_match('#\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d#sui', $cells[0], $time)) {
                            $model->deadline_seconds = strtotime($time[0]) - time();
                        }

                        preg_match('#\?data\=(\d+)#sui', $cells[0], $id);
                        $model->id = $id[1];

                        preg_match_all('#<a.*?>(.+?)</a>#sui', $cells[0], $name);
                        $model->name = $name[1][1];

                        $model->role = $condition->role;
                        $model->age = $cells[2];

                        $skill = explode("<span class='kva'>", $cells[6]);
                        $model->setSkill(0, (int)$skill[0], (int)$skill[1]);

                        $skill = explode("<span class='kva'>", $cells[7]);
                        $model->setSkill(1, (int)$skill[0], (int)$skill[1]);

                        $model->on_market = 1;
                        $model->sell_price = preg_replace('#[^\d]+#sui', '', $cells[1]);

                        $result[] = $model;
                    }
                }
            } else {
                break;
            }
        }

        return $result;
    }


    public function getFreeTeamsForFastGames()
    {
        $url = $this->factory->getRouter()->getFastGames();

        $text = $this->downloader->get($url);

        if (strpos($text, 'modalInputChallenge') === false) {
            $this->reauth();
            $text = $this->downloader->get($url);
        }

        $my_games = $free_games = 0;
        preg_match("#Free challenges: \d+/(\d+)#si", $text, $free_games);
        $free_games = $free_games[1];

        preg_match("#Daily limit: (\d+)#si", $text, $my_games);
        $my_games = $my_games[1];

        $text = $this->downloader->get($this->factory->getRouter()->getFastGames('day'));

        $teams = [];

        if (preg_match("#<table cellspacing='0' cellpadding='0' class='table' id='table-1'>.+?<tbody>(.+?)</tbody>#si", $text, $text)) {
            $text = $text[1];
            if (preg_match_all('#<tr.*?>(.+?)</tr>#si', $text, $rows)) {
                foreach ($rows[1] as $row) {
                    if (preg_match("#<td>(\d+)</td><td><a\s*rel='\#yesnoChallenge(\d+)'#si", $row, $team_data)) {
                        $teams[] = [
                            'id' => $team_data[2],
                            'strength' => $team_data[1]
                        ];
                    }
                }
            }
        }

        return [
            'teams' => $teams,
            'games_played' => $my_games,
            'free_games' => $free_games
        ];
    }

    public function playFastGame($team_id)
    {
        $url = $this->factory->getRouter()->playFastGame($team_id);

        $this->downloader->post($url, [
            'lineup' => 1,
            'tactics' => 1
        ]);
    }

    public function getAvailableCountForScouting()
    {
        $url = $this->factory->getRouter()->scoutingPage();

        $page = $this->downloader->get($url);

        if (!preg_match("#id='table-1.+?<tbody>(.+?)</table>#si", $page, $page)) {
            return 30;
        }

        return 30 - substr_count($page[1], '<tr>');
    }

    public function scoutPlayer($player_id)
    {
        $this->downloader->get($this->factory->getRouter()->getPlayer($player_id));
        $this->downloader->get($this->factory->getRouter()->scoutPlayer($player_id));
    }
}
