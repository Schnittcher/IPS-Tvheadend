<?php

require_once __DIR__ . '/libs/TVH.php';

class IPS_Tvheadend extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        //Connect to Websocket Client
        $this->RegisterPropertyString('TvhIP', '');
        $this->RegisterPropertyInteger('TvhPort', 9981);
        $this->RegisterPropertyString('TvhMac', '');
        $this->RegisterPropertyString('Broadcast', '');
        $this->RegisterPropertyString('ServerUsername', 'root');
        $this->RegisterPropertyString('ServerPassword', '');
        $this->RegisterPropertyInteger('UpdateTimerInterval', 20);

        $this->createVariablenProfiles();
        $this->RegisterVariableBoolean('TVHStatus','Server Status','TVH.ServerStatus',1);
        $this->RegisterVariableBoolean('TVHPower','Power','~Switch',2);
        $this->RegisterVariableInteger('TVHConnections','Verbindungen','',3);
        $this->RegisterVariableString('TVHSubscriptionsInfo','Subscriptions','~HTMLBox',4);
        $this->RegisterVariableString('TVHNextRecordingChannel','Nächste Aufnahme Kanal','',5);
        $this->RegisterVariableString('TVHNextRecording','Nächste Aufnahme','',6);
        $this->RegisterVariableInteger('TVHNextRecordingStartTime','Nächste Aufnahme Startzeit','~UnixTimestamp',7);
        $this->RegisterVariableInteger('TVHNextRecordingEndTime','Nächste Aufnahme Endzeit','~UnixTimestamp',8);

        $this->RegisterTimer('TVH_UpdateActuallyStatus', 0, 'TVH_updateActuallyStatus($_IPS[\'TARGET\']);');
   }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->SetTimerInterval('TVH_UpdateActuallyStatus', $this->ReadPropertyInteger('UpdateTimerInterval') * 1000);
        $this->EnableAction('TVHPower');

    }

    private function createVariablenProfiles()
    {
        //Online / Offline Profile
        $this->RegisterProfileBooleanEx('TVH.ServerStatus', 'Network', '', '', array(
            array(false, 'Offline',  '', 0xFF0000),
            array(true, 'Online',  '', 0x00FF00)
        ));
    }

    public function updateActuallyStatus()
    {
        $this->checkServerStatus();
        $this->getNextRecording();
        $this->getConnections();
    }

    public function checkServerStatus()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'));
        SetValue($this->GetIDForIdent('TVHStatus'),$TVH->getServerStatus());
        SetValue($this->GetIDForIdent('TVHPower'),$TVH->getServerStatus());
    }

    public function wakeUP()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'));
        $TVH->WakeOnLan($this->ReadPropertyString('Broadcast'));
    }

    public function shutdown()
    {
        set_include_path(__DIR__ . '/libs');
        require_once(__DIR__.'/libs/Net/SSH2.php');
        $ssh = new Net_SSH2($this->ReadPropertyString('TvhIP'));
        if (!$ssh->login($this->ReadPropertyString('ServerUsername'), $this->ReadPropertyString('ServerPassword'))) {
            exit('Login Failed');
        }
        @$ssh->exec('shutdown -h now');
    }

    public function getNextRecording()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'));
        $recordings = $TVH->getUpcomingRecordings();

        if (is_array($recordings)) {
            if(count($recordings['entries']) > 0) {
                $startTime = $recordings['entries'][0]['start'];
                $endTime = $recordings['entries'][0]['stop'];
                $channel = $recordings['entries'][0]['channelname'];
                $title = $recordings['entries'][0]['title']['ger']. " - ". $recordings['entries'][0]['subtitle']['ger'];
                SetValue($this->GetIDForIdent('TVHNextRecordingChannel'),$channel);
                SetValue($this->GetIDForIdent('TVHNextRecording'),$title);
                SetValue($this->GetIDForIdent('TVHNextRecordingStartTime'),$startTime);
                SetValue($this->GetIDForIdent('TVHNextRecordingEndTime'),$endTime);
            }
        }
    }

    public function getConnections()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'));
        $connections = $TVH->getSubscriptions();
        SetValue($this->GetIDForIdent('TVHConnections'),$connections['totalCount']);

        $htmlbox = '
        <style type="text/css">
            .Subscriptions{
                width:100%; 
                border-collapse:collapse; 
            }
            .Subscriptions td{ 
                padding:7px; border:#111c2d 3px solid;
            }
            /* provide some minimal visual accomodation for IE8 and below */
            .Subscriptions tr{
                background: #000000;
                text-align:center;
            }
            /*  Define the background color for all the ODD background rows  */
            .Subscriptions tr:nth-child(odd){ 
                background: #1d304f;
            }
            /*  Define the background color for all the EVEN background rows  */
            .Subscriptions tr:nth-child(even){
                background: #142135;
            }
        </style>
        <table class="Subscriptions">
	    <tbody>
		<tr>
		<th>User</th>
		<th>Host</th>
		<th>Startzeit</th>
		<th>Client</th>
		<th>Channel</th>
		<th>Profil</th>
		</tr>
		<tr>';
        if (is_array($connections)) {
            foreach ($connections['entries'] as $connection) {
                $htmlbox .= '<td class="odd">'.$connection['username'].'</td>';
                $htmlbox .= '<td>'.$connection['hostname'].'</td>';
                $htmlbox .= '<td>'.date("d.m.Y H:i",$connection['start']).'</td>';
                $htmlbox .= '<td>'.$connection['title'].'</td>';
                $htmlbox .= '<td>'.$connection['channel'].'</td>';
                $htmlbox .= '<td>'.$connection['profile'].'</td>';
                $htmlbox .= '</tr>';
            }
        }
        $htmlbox .=	'</tbody></table>';
        SetValue($this->GetIDForIdent('TVHSubscriptionsInfo'),$htmlbox);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'TVHPower':
                if ($Value) {
                        $this->SendDebug(__FUNCTION__.' TVH Wakeup', $Value, 0);
                        $this->wakeUP();
                        SetValue(IPS_GetObjectIDByIdent($Ident, $this->InstanceID), true);
                    } else {
                    $this->SendDebug(__FUNCTION__.' TVH Shutdown', $Value, 0);
                        $this->shutdown();
                        SetValue(IPS_GetObjectIDByIdent($Ident, $this->InstanceID), false);
                    }
                    break;
        }
    }

        protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 0) {
                throw new Exception('Variable profile type does not match for profile ' . $Name);
            }
        }
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

}
