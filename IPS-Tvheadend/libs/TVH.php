<?php

class TVH {

    private $ip;
    private $port;

    public function __construct($ip,$port,$mac)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->mac = $mac;
        return $this;
    }

    private function request($parm)
    {
        $url = "http://admin:tv@".$this->ip.":".$this->port."/".$parm;
        $json = @file_get_contents($url);
        if ($json === FALSE) {
            //throw new Exception("Cannot access '$url' to read contents.");
        } else {
             $data = json_decode($json, TRUE);
            return $data;
        }


    }

    public function getServerInfo()
    {
        return $this->request("api/serverinfo");
    }

    public function getSubscriptions()
    {
        return $this->request("api/status/subscriptions");
    }

    public function getConnections()
    {
        return $this->request("api/status/connections");
    }

    public function getInputs()
    {
        return $this->request("api/status/inputs");
    }

    public function getChannels()
    {
        return $this->request("api/channel/list");
    }

    public function getFinishedRecordings()
    {
        return $this->request("api/dvr/entry/grid_finished");
    }

    public function getUpcomingRecordings()
    {
        return $this->request("api/dvr/entry/grid_upcoming?sort=start");
    }

    public function getFailedRecordings()
    {
        return $this->request("api/dvr/entry/grid_failed");
    }

    public function getServerStatus()
    {
        return Sys_Ping($this->ip, 1000);
    }

    public function WakeOnLan($broadcast)
    {
        $addr = $broadcast; //"192.168.1.255";
        $addr_byte = explode(':', $this->mac);
        $hw_addr = '';

        for ($a=0; $a < 6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));

        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

        for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;

        // send it to the broadcast address using UDP
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($s == false)
        {
            echo "Error creating socket!\n";
            echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
        }
        else
        {
            // setting a broadcast option to socket:
            $opt_ret = socket_set_option($s, 1, 6, TRUE);
            if($opt_ret < 0)
            {
                echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
            }
            $e = socket_sendto($s, $msg, strlen($msg), 0, $addr, 2050);
            //echo $e;
            socket_close($s);

            //echo 'Der Server wird gestartet.';
            //echo "Magic Packet sent (".$e.") to ".$addr.", MAC=".$this->mac;
        }
    }

}
?>
