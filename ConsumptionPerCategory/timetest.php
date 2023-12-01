<?php

declare(strict_types=1);

if (defined('PHPUNIT_TESTSUITE')) {
    trait TestTime
    {
        private $currentTime = 989884800; //May 15 2001

        public function setTime(int $Time)
        {
            $this->currentTime = $Time;
        }

        protected function getTime()
        {
            return $this->currentTime;
        }
    }
} else {
    trait TestTime
    {
        protected function getTime()
        {
            return time();
        }
    }
}