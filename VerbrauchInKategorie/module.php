<?php

declare(strict_types=1);
//include_once __DIR__ . '/timetest.php';
class VerbrauchInKategorie extends IPSModule
{
    //use TestTime;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterVariableInteger('StartTime', $this->Translate('Start Time'), '~UnixTimestamp', 0);
        $this->RegisterVariableInteger('EndTime', $this->Translate('End Time'), '~UnixTimestamp', 1);

        $this->RegisterPropertyString('Sources', '[]');
        $this->RegisterPropertyBoolean('CheckIntervall', false);
        $this->RegisterPropertyInteger('Intervall', 0);

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

        $source = json_decode($this->ReadPropertyString('Sources'), true);
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
        $sources = json_decode($this->ReadPropertyString('Sources'), true);

        //Get Values
        foreach ($sources as $key => $source) {
            if (IPS_VariableExists($source['SourceVariable'])) {
                $loggedValue = AC_GetLoggedValues($archiveID, $source['SourceVariable'], $this->GetValue('StartTime'), $this->GetValue('EndTime'), 0);
                $sources[$key]['Value'] = array_sum(array_column($loggedValue, 'Value'));
            } else {
                $this->SetStatus(201);
                return;
            }
        }
        $totalConsumption = array_sum(array_column($sources, 'Value'));

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