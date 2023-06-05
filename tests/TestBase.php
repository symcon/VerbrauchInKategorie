<?php

declare(strict_types=1);

define('VAR_BOOL', 0);
define('VAR_INT', 1);
define('VAR_FLOAT', 2);
define('VAR_STRING', 3);

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/MessageStubs.php';
include_once __DIR__ . '/stubs/ConstantStubs.php';

use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    protected $archiveControlID;
    protected $categoryInstanceID;

    protected function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();

        //Register our core stubs for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/stubs/CoreStubs/library.json');

        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

        //Register required profiles
        IPS_CreateVariableProfile('~UnixTimestamp', VAR_INT);

        $this->archiveControlID = IPS_CreateInstance('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
        $this->categoryInstanceID = IPS_CreateInstance('{51CA1560-4725-A7F2-A449-94B7812E1986}');

        parent::setUp();
    }
}