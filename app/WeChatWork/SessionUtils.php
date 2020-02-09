<?php
declare(strict_types=1);

namespace App\WeChatWork;


use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;

class SessionUtils
{
    const SESSION_KEY_PREFIX = "wechat_work";

    /**
     * @var SessionManager|Store
     */
    private $session;

    private $applicationId;

    public function __construct($session, $applicationId)
    {
        $this->session = $session;
        $this->applicationId = $applicationId;
    }

    /**
     * @return int|string|null
     */
    public function getUserId()
    {
        return $this->session->get(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".user_id");
    }

    public function setUserId($userId)
    {
        $this->session->put(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".user_id", $userId);
    }

    public function getUser()
    {
        return $this->session->get(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".user");
    }

    public function setUser($user)
    {
        $this->session->put(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".user", $user);
    }

    public function getOpenId()
    {
        return $this->session->get(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".open_id");
    }

    public function setOpenId($openId)
    {
        $this->session->put(self::SESSION_KEY_PREFIX . ".app." . $this->applicationId . ".open_id", $openId);
    }
}
