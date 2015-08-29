<?php

namespace ppm;

class Logger
{
    public function write($message)
    {
        print date('H:i:s') . ' - ' . $message . "\r\n";
        flush();
    }
}

class NullLogger extends Logger
{
    public function write($message)
    {

    }
}
