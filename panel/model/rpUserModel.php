<?php

class rpUserModel extends lpPDOModel
{
    static protected $metaData = null;

    const NO = "no";
    const STD = "std";
    const EXT = "ext";
    const FREE = "free";

    static protected function metaData()
    {
        if(!self::$metaData) {
            self::$metaData = [
                "db" => rpApp::getDB(),
                "table" => "user",
                "engine" => "MyISAM",
                "charset" => "utf8",
                self::PRIMARY => "id"
            ];

            self::$metaData["struct"] = [
                "id" => ["type" => self::AI],
                "uname" => ["type" => self::VARCHAR, "length" => 256],
                "type" => ["type" => self::VARCHAR, "length" => 256],
                "passwd" => ["type" => self::TEXT],
                "email" => ["type" => self::TEXT],
                "qq" => ["type" => self::TEXT],
                "settings" => ["type" => self::JSON],
                "regtime" => ["type" => self::UINT],
                "expired" => ["type" => self::UINT],
                "lastLoginTime" => ["type" => self::UINT, "default" => 0],
                "lastLoginIP" => ["type" => self::TEXT, self::NOTNULL => false],
                "lastLoginUA" => ["type" => self::TEXT, self::NOTNULL => false],
            ];

            foreach(self::$metaData["struct"] as &$v)
                if(!isset($v[self::NOTNULL]))
                    $v[self::NOTNULL] = true;
        }

        return self::$metaData;
    }

    static public function byUName($uname)
    {
        return new self(self::find(["uname" => $uname])["id"]);
    }

    static public function this()
    {
        return self::byUName(rpAuth::uname());
    }

    public function isAllowToPanel()
    {
        if($this->data["type"] != self::NO && !$this->isAdmin())
            return true;
        else
            return false;
    }

    public function isAdmin()
    {
        global $rpCfg;

        return array_key_exists($this->data["uname"], $rpCfg["Admins"]);
    }
}