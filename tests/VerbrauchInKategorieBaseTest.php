<?php

declare(strict_types=1);

include_once __DIR__ . '/TestBase.php';

class VerbrauchInKategorieBaseTest extends TestBase
{
    public function testCalculationTwoCategoriesTwoVariables()
    {
        IPS_EnableDebug($this->categoryInstanceID, 6000);

        //Variables
        SetValue(IPS_GetObjectIDByIdent('StartTime', $this->categoryInstanceID), strtotime('May 7 2001 00:00:00'));
        SetValue(IPS_GetObjectIDByIdent('EndTime', $this->categoryInstanceID), strtotime('May 14 2001 00:00:00'));
        //2 source variables
        $sourceVariableOne = IPS_CreateVariable(VAR_INT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableOne, true);
        $sourceVariableTwo = IPS_CreateVariable(VAR_FLOAT);
        AC_SetLoggingStatus($this->archiveControlID, $sourceVariableTwo, true);
        //set archive values to the variables

        /* Umbau von Rohwerten zu Aggregierten Werten
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
        AC_AddLoggedValues($this->archiveControlID, $sourceVariableTwo, $loggedValues);*/

        $aggregationPeriodDay = [
            [
                'Avg'       => 1,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 07 2001 00:00:00'),
            ],
            [
                'Avg'       => 5,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 08 2001 00:00:00'),
            ],
            [
                'Avg'       => 9,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 09 2001 00:00:00'),
            ],
            [
                'Avg'       => 8,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 10 2001 00:00:00'),
            ],
            [
                'Avg'       => 6,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 11 2001 00:00:00'),
            ],
            [
                'Avg'       => 2,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 12 2001 00:00:00'),
            ],
            [
                'Avg'       => 3,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 13 2001 00:00:00'),
            ],
        ];

        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableOne, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 7,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableTwo, 1, $aggregationPeriodDay);

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
        $this->assertEqualsWithDelta(47.22, GetValue(IPS_GetObjectIDByIdent('CategoryOne', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(52.77, GetValue(IPS_GetObjectIDByIdent('CategoryTwo', $this->categoryInstanceID)), 0.01);

        /**
         * Manuel Calculation
         * totalConsumption = (1+5+9+8+6+2+3) + (1+5+9+8+6+2+7) = 72
         * categoryOne = 1+5+9+8+6+2+3 = 34
         * categoryTwo = 1+5+9+8+6+2+7 = 38
         *
         * percent categoryOne = (34/72) * 100 = ~47,22%
         * percent categoryTwo = (38/72) * 100 = ~52,77%
         */
    }

    public function testWithSevenVariablesAndThreeCategories()
    {
        IPS_EnableDebug($this->categoryInstanceID, 6000);

        //Variables
        SetValue(IPS_GetObjectIDByIdent('StartTime', $this->categoryInstanceID), strtotime('May 7 2001 00:00:00'));
        SetValue(IPS_GetObjectIDByIdent('EndTime', $this->categoryInstanceID), strtotime('May 14 2001 00:00:00'));
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
        /* Umbau von Rohwerten zu Aggregierten Werten
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

        AC_AddLoggedValues($this->archiveControlID, $sourceVariableSeven, [['TimeStamp' => 989884865, 'Value'=> 100]]); */

        $aggregationPeriodDay = [
            [
                'Avg'       => 7,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 07 2001 00:00:00'),
            ],
            [
                'Avg'       => 5,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 08 2001 00:00:00'),
            ],
            [
                'Avg'       => 3,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 09 2001 00:00:00'),
            ],
            [
                'Avg'       => 6,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 10 2001 00:00:00'),
            ],
            [
                'Avg'       => 2,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 11 2001 00:00:00'),
            ],
            [
                'Avg'       => 4,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 12 2001 00:00:00'),
            ],
            [
                'Avg'       => 8,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 13 2001 00:00:00'),
            ],
        ];

        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableOne, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 9,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableTwo, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 6,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableThree, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 3,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableFour, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 2,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableFive, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 1,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableSix, 1, $aggregationPeriodDay);

        array_pop($aggregationPeriodDay);
        $aggregationPeriodDay[] = [
            'Avg'       => 10,
            'Duration'  => 24 * 60 * 60,
            'Max'       => 0,
            'MaxTime'   => 0,
            'Min'       => 0,
            'MinTime'   => 0,
            'TimeStamp' => strtotime('May 13 2001 00:00:00'),
        ];
        AC_StubsAddAggregatedValues($this->archiveControlID, $sourceVariableSeven, 1, $aggregationPeriodDay);

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
        $this->assertEqualsWithDelta(45.61, GetValue(IPS_GetObjectIDByIdent('CategoryOne', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(38.15, GetValue(IPS_GetObjectIDByIdent('CategoryTwo', $this->categoryInstanceID)), 0.01);
        $this->assertEqualsWithDelta(16.22, GetValue(IPS_GetObjectIDByIdent('CategoryThree', $this->categoryInstanceID)), 0.01);

        /**
         * Manuel Calculation
         * totalConsumption = 104+87+37 = 228
         * categoryOne = (7+5+3+6+2+4) * 3 + 8+9+6 = 104
         * categoryTwo =  (7+5+3+6+2+4)*3 + 3+2+1 = 87
         * categoryThree = 7+5+3+6+2+4+10 = 37
         *
         * percent categoryOne = (104/228)*100 = ~45.61%
         * percent categoryTwo = (87/228)* 100 = ~38.15%
         * percent categoryThree = (37/228)*100 = ~16.22%
         */
    }
}