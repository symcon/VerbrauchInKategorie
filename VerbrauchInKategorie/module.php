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
        $this->RegisterPropertyBoolean('CheckIntervall', false);
        $this->RegisterPropertyInteger('Intervall', 0);

        //For compatibility check if the ProgressProfile exist
        if (!IPS_VariableProfileExists('~Progress')) {
            IPS_CreateVariableProfile('~Progress', VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileValues('~Progress', 0, 100, 0.1);
            IPS_SetVariableProfileDigits('~Progress', 1);
            IPS_SetVariableProfileText('~Progress', '', ' %');
        }

        $this->RegisterTimer('UpdateCalculation', 0, 'VIK_CalculateConsumption($_IPS[\'TARGET\']);');

        //set an initial time
        $this->SetValue('StartTime', strtotime('yesterday'));
        $this->SetValue('EndTime', $this->getTime());
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

        $source = json_decode($this->ReadPropertyString('SourceVariables'), true);
        $currentCategories = array_diff(IPS_GetChildrenIDs($this->InstanceID), [$this->GetIDForIdent('StartTime'), $this->GetIDForIdent('EndTime')]);
        //change IDs to Idents
        foreach ($currentCategories as $key => $category) {
            $currentCategories[$key] = IPS_GetObject($category)['ObjectIdent'];
        }

        //Create the Variables
        foreach ($source as $key => $row) {
            $category = $row['Category'];
            if ($category == '') {
                $this->SetStatus(200);
                return;
            }
            //Add Categories if they aren't under the instance
            if (!in_array($category, $currentCategories)) {
                $this->MaintainVariable('Category' . str_replace(' ', '', $category), $category, VARIABLETYPE_FLOAT, '~Progress', 0, true);
            }
            $source[$key]['Category'] = 'Category' . str_replace(' ', '', $category);
        }
        $this->SetStatus(102);
        //remove Categories that aren't lists
        $notListed = array_diff($currentCategories, array_column($source, 'Category'));
        foreach ($notListed as $key => $value) {
            $this->UnregisterVariable($value);
        }

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
                $this->SendDebug($Ident, 'You try to set an automatic variable', 0);
                break;
        }
    }

    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $form['elements'][0]['items'][1]['visible'] = $this->ReadPropertyBoolean('CheckIntervall'); //Visible of Intervall
        return json_encode($form);
    }

    public function UIVisible(bool $value)
    {
        $this->UpdateFormField('Intervall', 'visible', $value);
    }

    public function CalculateConsumption()
    {
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $sources = json_decode($this->ReadPropertyString('SourceVariables'), true);

        //Validate that the startTime is lower than endTime
        $startTime = $this->GetValue('StartTime');
        $endTime = $this->GetValue('EndTime');
        if ($endTime > 0 && $endTime < $startTime) {
            $this->SetStatus(202);
            return;
        } else {
            $this->SetStatus(102);
        }

        //Get Values
        foreach ($sources as $key => $source) {
            if (IPS_VariableExists($source['SourceVariable'])) {
                $loggedValue = AC_GetAggregatedValues($archiveID, $source['SourceVariable'], 1 /*Daily*/, $startTime, $endTime, 0);
                $sources[$key]['Value'] = array_sum(array_column($loggedValue, 'Avg'));

                //Debugs
                //$this->SendDebug('Aggregated Values of ' . $source['SourceVariable'], print_r($loggedValue, true), 0);
                $this->SendDebug('Sum of ' . $source['SourceVariable'], '' . $sources[$key]['Value'], 0);
            } else {
                $this->SetStatus(201);
                return;
            }
        }
        $this->SetStatus(102);
        $totalConsumption = array_sum(array_column($sources, 'Value'));

        //Debugs
        $this->SendDebug('Total Consumption', '' . $totalConsumption, 0);

        //Get Values per Category
        $categories = [];
        foreach ($sources as $key => $source) {
            if (!array_key_exists($source['Category'], $categories)) {
                $categories[$source['Category']] = 0;
            }
            $categories[$source['Category']] += $source['Value'];
        }

        //Calculate the percent
        foreach ($categories as $category => $value) {
            if ($totalConsumption > 0) {
                $percent = ($value / $totalConsumption) * 100;
                $this->SetValue('Category' . str_replace(' ', '', $category), $percent);
            } else {
                $this->SetValue('Category' . str_replace(' ', '', $category), 0);
            }
        }

        //Reset the Timer
        if ($this->ReadPropertyBoolean('CheckIntervall')) {
            $this->SetTimerInterval('UpdateCalculation', $this->ReadPropertyInteger('Intervall') * 60 * 1000);
        }
    }
}