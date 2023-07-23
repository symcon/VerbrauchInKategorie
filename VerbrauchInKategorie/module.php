<?php

declare(strict_types=1);
include_once __DIR__ . '/timetest.php';
class VerbrauchInKategorie extends IPSModule
{
    use TestTime;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Register Variables
        $this->RegisterVariableInteger('StartTime', $this->Translate('Start Time'), '~UnixTimestampDate', 100);
        $this->EnableAction('StartTime');
        $this->RegisterVariableInteger('EndTime', $this->Translate('End Time'), '~UnixTimestampDate', 101);
        $this->EnableAction('EndTime');

        //Register Properties
        $this->RegisterPropertyString('SourceVariables', '[]');
        $this->RegisterPropertyInteger('Interval', 0);

        //For compatibility check if our profile exist
        if (!IPS_VariableProfileExists('Progress.CIC')) {
            IPS_CreateVariableProfile('Progress.CIC', VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileValues('Progress.CIC', 0, 100, 0.1);
            IPS_SetVariableProfileDigits('Progress.CIC', 1);
            IPS_SetVariableProfileText('Progress.CIC', '', ' %');
        }

        $this->RegisterTimer('UpdateCalculation', 0, 'VIK_CalculateConsumption($_IPS[\'TARGET\']);');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $sourceVariables = json_decode($this->ReadPropertyString('SourceVariables'), true);

        // make sanity check if category names are defined
        foreach ($sourceVariables as $row) {
            if (!$row['Category']) {
                $this->SetStatus(200);
                return;
            }
        }

        // make array of current categories which will be removed in the next block, if still valid
        $remainingCategories = [];
        foreach (IPS_GetChildrenIDs($this->InstanceID) as $id) {
            $remainingCategories[] = IPS_GetObject($id)['ObjectIdent'];
        }

        // remove special ident's which shall never be removed
        $remainingCategories = array_diff($remainingCategories, ['StartTime', 'EndTime']);

        // create the category variables
        foreach ($sourceVariables as $row) {
            // sanitize string category to A-Z, a-z, 0-9 and _
            $ident = $this->sanitizeNameToIdent($row['Category']);
            $this->RegisterVariableFloat($ident, $row['Category'], '~Progress', 0);

            $remainingCategories = array_diff($remainingCategories, [$ident]);
        }

        // remove categories that aren't in the list anymore
        foreach ($remainingCategories as $ident) {
            $this->UnregisterVariable($ident);
        }

        // set instance active
        $this->SetStatus(102);

        // update the Timer
        $this->SetTimerInterval('UpdateCalculation', $this->ReadPropertyInteger('Interval') * 60 * 1000);

        // make initial calculation
        $this->CalculateConsumption();
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'StartTime':
            case 'EndTime':
                $this->SetValue($Ident, $Value);
                $this->CalculateConsumption();
                break;
            default:
                $this->SendDebug($Ident, strval('You try to set an automatic variable'), 0);
                break;
        }
    }

    public function CalculateConsumption()
    {
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

        $sourceVariables = json_decode($this->ReadPropertyString('SourceVariables'), true);

        // validate that the startTime is lower than endTime
        $startTime = $this->GetValue('StartTime');
        $endTime = $this->GetValue('EndTime');
        if ($endTime > 0 && $endTime < $startTime) {
            echo $this->Translate('The start time ist greater then the end time');
            $this->SetStatus(202);
            return;
        }

        // validate that all variables are valid
        foreach ($sourceVariables as $row) {
            if (!IPS_VariableExists($row['SourceVariable'])) {
                echo $this->Translate('A variable is invalid');
                $this->SetStatus(201);
                return;
            }
        }

        // activate instance
        $this->SetStatus(102);

        // get values from archive
        foreach ($sourceVariables as &$row) {
            $loggedValue = AC_GetAggregatedValues($archiveID, $row['SourceVariable'], 1 /*Daily*/, $startTime, $endTime, 0);
            $row['Value'] = array_sum(array_column($loggedValue, 'Avg'));
            $this->SendDebug('Sum of ' . $row['SourceVariable'], strval($row['Value']), 0);
        }

        // calculate the total
        $totalConsumption = array_sum(array_column($sourceVariables, 'Value'));
        $this->SendDebug('Total Consumption', strval($totalConsumption), 0);

        // calculate values per category
        $categories = [];
        foreach ($sourceVariables as $source) {
            if (!array_key_exists($source['Category'], $categories)) {
                $categories[$source['Category']] = 0;
            }
            $categories[$source['Category']] += $source['Value'];
        }

        // calculate the percent per category
        foreach ($categories as $category => $value) {
            $ident = $this->sanitizeNameToIdent($category);
            if ($totalConsumption > 0) {
                $this->SetValue($ident, ($value / $totalConsumption) * 100);
            } else {
                $this->SetValue($ident, 0);
            }
        }
    }

    private function sanitizeNameToIdent($name)
    {
        return 'Category' . preg_replace('/[^A-Za-z0-9_]/', '', $name);
    }
}
