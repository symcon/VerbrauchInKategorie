<?php

declare(strict_types=1);

include_once __DIR__ . '/TestBase.php';

class VerbrauchInKategorieBaseTest extends TestBase
{
    public function testCalculationTwoCategoriesTwoVariables()
    {
        //Variables
        SetValue(IPS_GetObjectIDByIdent('StartTime', $this->categoryInstanceID), 989884799);
        SetValue(IPS_GetObjectIDByIdent('EndTime', $this->categoryInstanceID), 989884921);
        //2 source variables
        $sourceVariableOne = IPS_CreateVariable(VAR_INT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableOne, true);
        $sourceVariableTwo = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableTwo, true);
        //set archive values to the variables
        $loggedValues = [
            ['TimeStamp' => 989884800, 'Value' => 15],
            ['TimeStamp' => 989884860, 'Value' => 30],
            ['TimeStamp' => 989884920, 'Value' => 45]
        ];

        AC_AddLoggedValues($this->archiveControlID, $sourceVariableOne, $loggedValues);

        $loggedValues = [
            ['TimeStamp' => 989884860, 'Value' => 30],
            ['TimeStamp' => 989884920, 'Value' => 45]
        ];
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableTwo, $loggedValues);

        //Properties
        IPS_SetProperty($this->categoryInstanceID, 'SourceVariables', json_encode(
            [
                ['SourceVariable' => $sourceVariableOne, 'Category' => 'One'],
                ['SourceVariable' => $sourceVariableTwo, 'Category' =>'Two']
            ]
        ));

        IPS_ApplyChanges($this->categoryInstanceID);

        $this->assertTrue(IPS_VariableExists(IPS_GetObjectIDByIdent('CategoryOne', $this->categoryInstanceID)));
        $this->assertEquals(102, IPS_GetInstance($this->categoryInstanceID)['InstanceStatus']);
        $this->assertEqualsWithDelta(54.54, GetValue(IPS_GetObjectIDByIdent('CategoryOne', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(45.45, GetValue(IPS_GetObjectIDByIdent('CategoryTwo', $this->categoryInstanceID)), 0.01);

        /**
         * Manuel Calculation
         * totalConsumption = 15+30+45+30+45 = 165
         * categoryOne = 90
         * categoryTwo = 75
         *
         * percent categoryOne = (90/165)*100 = ~54,54%
         * percent categoryTwo = (75/165)* 100 = ~45,45%
         */
    }

    public function testWithSevenVariablesAndThreeCategories()
    {
        //Variables
        SetValue(IPS_GetObjectIDByIdent('StartTime', $this->categoryInstanceID), 989884799);
        SetValue(IPS_GetObjectIDByIdent('EndTime', $this->categoryInstanceID), 989884921);
        //7 source variables
        $sourceVariableOne = IPS_CreateVariable(VAR_INT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableOne, true);

        $sourceVariableTwo = IPS_CreateVariable(VAR_INT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableTwo, true);

        $sourceVariableThree = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableThree, true);

        $sourceVariableFour = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableFour, true);

        $sourceVariableFive = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableFive, true);

        $sourceVariableSix = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableSix, true);

        $sourceVariableSeven = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableSeven, true);

        //set archive values to the variables
        $loggedValues = [
            ['TimeStamp' => 989884800, 'Value' => 15],
            ['TimeStamp' => 989884860, 'Value' => 30],
            ['TimeStamp' => 989884920, 'Value' => 45]
        ];

        AC_AddLoggedValues($this->archiveControlID, $sourceVariableOne, $loggedValues);
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableTwo, $loggedValues);
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableThree, $loggedValues);

        $loggedValues = [
            ['TimeStamp' => 989884860, 'Value' => 30],
            ['TimeStamp' => 989884920, 'Value' => 45]
        ];

        AC_AddLoggedValues($this->archiveControlID, $sourceVariableFour, $loggedValues);
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableFive, $loggedValues);
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableSix, $loggedValues);

        AC_AddLoggedValues($this->archiveControlID, $sourceVariableSeven, [['TimeStamp' => 989884865, 'Value'=> 100]]);

        //Properties
        IPS_SetProperty($this->categoryInstanceID, 'SourceVariables', json_encode(
            [
                ['SourceVariable' => $sourceVariableOne, 'Category' => 'One'],
                ['SourceVariable' => $sourceVariableTwo, 'Category' =>'One'],
                ['SourceVariable' => $sourceVariableThree, 'Category' => 'One'],
                ['SourceVariable' => $sourceVariableFour, 'Category' =>'Two'],
                ['SourceVariable' => $sourceVariableFive, 'Category' => 'Two'],
                ['SourceVariable' => $sourceVariableSix, 'Category' =>'Two'],
                ['SourceVariable' => $sourceVariableSeven, 'Category' =>'Three']
            ]
        ));

        IPS_ApplyChanges($this->categoryInstanceID);

        $this->assertEquals(102, IPS_GetInstance($this->categoryInstanceID)['InstanceStatus']);
        $this->assertEqualsWithDelta(45.37, GetValue(IPS_GetObjectIDByIdent('CategoryOne', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(37.81, GetValue(IPS_GetObjectIDByIdent('CategoryTwo', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(16.80, GetValue(IPS_GetObjectIDByIdent('CategoryThree', $this->categoryInstanceID)), 0.01);

        /**
         * Manuel Calculation
         * totalConsumption = (15+30+45)*3+(30+45)*3+100 = 595
         * categoryOne = 270
         * categoryTwo = 225
         * categoryThree = 100
         *
         * percent categoryOne = (270/595)*100 = ~45,37
         * percent categoryTwo = (225/595)* 100 = ~37,81%
         * percent categoryThree = (100/595)*100 = ~16,80%
         */
    }
}