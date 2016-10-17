<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2016 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2014-2016 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    simpletipp
 * @license    LGPL
 * @filesource
 */

namespace Simpletipp\Modules;

use Contao\Input;
use Contao\MemberModel;
use Simpletipp\SimpletippModule;
use Simpletipp\TelegramCommander;
use Telegram\Bot\Actions;

/**
 * Class SimpletippTelegram
 *
 * @copyright  Martin Kozianka 2014-2016
 * @author     Martin Kozianka <martin@kozianka.de>
 * @package    Controller
 */

class SimpletippTelegram extends SimpletippModule
{
    private $chatMember;
    private $telegram;

    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $this->Template = new \BackendTemplate('be_wildcard');
            $this->Template->wildcard = '### SimpletippTelegram ###';
            return $this->Template->parse();
        }

        if ($this->simpletipp_telegram_url_token !== Input::get('token'))
        {
            die('Missing token');
            exit;
        }
        $this->strTemplate = $this->simpletipp_template;
        return parent::generate();
	}

    protected function compile()
    {
        $this->commander = new TelegramCommander($this->simpletipp_telegram_bot_key);

        $this->text = $this->commander->getText();
        if ($this->text === null) {
            // Only handle text messages
            exit;
        }

        if (strpos($this->text, "/start") === 0) {
            // Handle start command
            $this->handleStart();
        }
        elseif ($this->commander->getChatMember() === null) {
            $this->commander->sendText('Chat not registered.');            
            exit;
        }

        $t = strtolower($this->text);
        switch ($t) {
            case "h":
                $this->showHighscore();
                break;
            case "t":
                $this->handleTipp(true);
                break;
            case "s":
                $this->showSpiele();
                break;
            default:
                if(false) { // TODO Check if match_id is correct and "fresh"
                    $this->handleTipp();
                }
                else {
                    // Do something funny!
                    // Zeigler, Foto, Zitat, Sticker... 
                }
        }
        exit;
    }

    private function handleTipp($isInitial = false) {
        $this->commander->chatAction(Actions::TYPING);
        
        // Trage einen Tipp ein und zeige das nächste Spiel
    }

    private function showHighscore() {
        $this->commander->chatAction(Actions::TYPING);
        // Zeige den Highscore
    }

    private function showSpiele() {
        $this->commander->chatAction(Actions::TYPING);
        // Zeige die Spiele des aktuellen Spieltags      
    }

    private function handleStart() {
        $this->commander->chatAction(Actions::TYPING);

        // Verarbeite das Start-Kommando mit dem bot secret
        $botSecret = trim(str_replace("/start", "", $this->text));
        if (strlen($botSecret) === 0) {
            $this->commander->sendText("Missing secret key. Use link on settings page to start chat.");
            return false;
        }
        // Search for key in tl_member
        $objMember = MemberModel::findOneBy('simpletipp_bot_secret', $botSecret);
        if ($objMember === null) {
            $this->commander->sendText("Key not found.");
            return false;
        }
        $objMember->telegram_chat_id      = $this->commander->getChatId();
        $objMember->simpletipp_bot_secret = '';
        $objMember->save();

        $tmpl = 'Chat registered for %s (%s).';
        $this->commander->sendText(sprintf($tmpl, $objMember->firstname.' '.$objMember->lastname, $objMember->username));
        $this->commander->sendInfoMessage();
        return true;
    }
    
}
