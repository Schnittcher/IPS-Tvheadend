<?php

require_once __DIR__ . '/libs/TVH.php';

class IPS_Tvheadend extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('TvhIP', '');
        $this->RegisterPropertyInteger('TvhPort', 9981);
        $this->RegisterPropertyString('TvhMac', '');
        $this->RegisterPropertyString('Broadcast', '');
        $this->RegisterPropertyString('ServerUsername', 'root');
        $this->RegisterPropertyString('ServerPassword', '');
        $this->RegisterPropertyString('WebinterfaceUsername', 'admin');
        $this->RegisterPropertyString('WebinterfacePassword', '');

        $this->RegisterPropertyInteger('StartTimeRecording', 5);
        $this->RegisterPropertyInteger('EndTimeRecording', 5);
        $this->RegisterPropertyInteger('UpdateTimerInterval', 20);

        $this->createVariablenProfiles();
        $this->RegisterVariableBoolean('TVHStatus','Server Status','TVH.ServerStatus',1);
        $this->RegisterVariableBoolean('TVHPower','Power','~Switch',2);
        $this->RegisterVariableInteger('TVHConnections','Verbindungen','',3);
        $this->RegisterVariableInteger('TVHSubscriptions','Subscriptions','',4);
        $this->RegisterVariableString('TVHSubscriptionsInfo','Subscription Infos','~HTMLBox',5);
        $this->RegisterVariableString('TVHNextRecordingChannel','Nächste Aufnahme Kanal','',6);
        $this->RegisterVariableString('TVHNextRecording','Nächste Aufnahme','',7);
        $this->RegisterVariableInteger('TVHNextRecordingStartTime','Nächste Aufnahme Startzeit','~UnixTimestamp',8);
        $this->RegisterVariableInteger('TVHNextRecordingEndTime','Nächste Aufnahme Endzeit','~UnixTimestamp',9);
        $this->RegisterVariableBoolean('TVHActiveRecording','Aktive Aufnahme','TVH.ActiveRecording',10);

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
        $this->RegisterProfileBooleanEx('TVH.ActiveRecording', 'TV', '', '', array(
            array(false, 'Nein',  '', 0xFF0000),
            array(true, 'Ja',  '', 0x00FF00)
        ));
    }

    public function updateActuallyStatus()
    {
        $this->checkActiveRecording();
        $this->checkServerStatus();
        $this->getNextRecording();
        $this->getConnections();
        $this->getSubscriptions();
    }

    public function checkServerStatus()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'),$this->ReadPropertyString('WebinterfaceUsername'),$this->ReadPropertyString('WebinterfacePassword'));
        SetValue($this->GetIDForIdent('TVHStatus'),$TVH->getServerStatus());
        SetValue($this->GetIDForIdent('TVHPower'),$TVH->getServerStatus());
    }

    public function wakeUP()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'),$this->ReadPropertyString('WebinterfaceUsername'),$this->ReadPropertyString('WebinterfacePassword'));
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
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'),$this->ReadPropertyString('WebinterfaceUsername'),$this->ReadPropertyString('WebinterfacePassword'));
        $recordings = $TVH->getUpcomingRecordings();

        if (is_array($recordings)) {
            if(count($recordings['entries']) > 0) {
                $startTime = $recordings['entries'][0]['start'];
                $endTime = $recordings['entries'][0]['stop'];
                $channel = $recordings['entries'][0]['channelname'];
                $title = $recordings['entries'][0]['title']['ger']. " - ". $recordings['entries'][0]['disp_subtitle'];
                SetValue($this->GetIDForIdent('TVHNextRecordingChannel'),$channel);
                SetValue($this->GetIDForIdent('TVHNextRecording'),$title);
                SetValue($this->GetIDForIdent('TVHNextRecordingStartTime'),$startTime);
                SetValue($this->GetIDForIdent('TVHNextRecordingEndTime'),$endTime);
            } else {
                SetValue($this->GetIDForIdent('TVHNextRecordingChannel'),'');
                SetValue($this->GetIDForIdent('TVHNextRecording'),'Keine Aufnahme geplant');
                SetValue($this->GetIDForIdent('TVHNextRecordingStartTime'),0);
                SetValue($this->GetIDForIdent('TVHNextRecordingEndTime'),0); 
            }
        }
    }

    public function getConnections()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'),$this->ReadPropertyString('WebinterfaceUsername'),$this->ReadPropertyString('WebinterfacePassword'));
        $connections = $TVH->getConnections();
        SetValue($this->GetIDForIdent('TVHConnections'),$connections['totalCount']);
    }


    public function getSubscriptions()
    {
        $TVH = new TVH($this->ReadPropertyString('TvhIP'),$this->ReadPropertyInteger('TvhPort'),$this->ReadPropertyString('TvhMac'),$this->ReadPropertyString('WebinterfaceUsername'),$this->ReadPropertyString('WebinterfacePassword'));
        $connections = $TVH->getSubscriptions();
        SetValue($this->GetIDForIdent('TVHSubscriptions'),$connections['totalCount']);

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
                if (array_key_exists ('username', $connection)) {
                    $htmlbox .= '<td class="odd">'.$connection['username'].'</td>';
                    $htmlbox .= '<td>'.$connection['hostname'].'</td>';
                    $htmlbox .= '<td>'.date("d.m.Y H:i",$connection['start']).'</td>';
                    $htmlbox .= '<td>'.$connection['title'].'</td>';
                    $htmlbox .= '<td>'.$connection['channel'].'</td>';
                    $htmlbox .= '<td>'.$connection['profile'].'</td>';
                    $htmlbox .= '</tr>';
                }
            }
        }
        $htmlbox .=	'</tbody></table>';
        SetValue($this->GetIDForIdent('TVHSubscriptionsInfo'),$htmlbox);
    }

    private function checkActiveRecording() {
        $RecordingStartTime = GetValue($this->GetIDForIdent('TVHNextRecordingStartTime'));
        $RecordingEndTime = GetValue($this->GetIDForIdent('TVHNextRecordingEndTime'));

        $RecordingStartTime = (int)$RecordingStartTime+($this->ReadPropertyInteger('StartTimeRecording')*60);
        $RecordingEndTime = (int)$RecordingEndTime+($this->ReadPropertyInteger('EndTimeRecording')*60);

        if ($RecordingStartTime <= time()) {
            $this->SendDebug(__FUNCTION__. "EndTime", "Aktuelle Zeit: ".time(),0);
            $this->SendDebug(__FUNCTION__. "EndTime", "Aufnahme Startzeit: ".$RecordingEndTime,0);

            SetValue($this->GetIDForIdent('TVHActiveRecording'), true);
        }

        if ($RecordingEndTime >= time()) {
            $this->SendDebug(__FUNCTION__. "EndTime", "Aktuelle Zeit: ".time(),0);
            $this->SendDebug(__FUNCTION__. "EndTime", "Aufnahme Endzeit: ".$RecordingEndTime,0);
            SetValue($this->GetIDForIdent('TVHActiveRecording'), false);
        }
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
