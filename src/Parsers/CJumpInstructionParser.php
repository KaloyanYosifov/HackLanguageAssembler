<?php

namespace HackAssembler\Parsers;

use HackAssembler\MapRegister;

class CJumpInstructionParser
{
    public function handle(string $line, MapRegister $mapRegister): string
    {
        if (str_starts_with($line, '@')) {
            return '';
        }

        if (str_contains($line, ';')) {
            $line = explode(';', $line)[1];
        }

        $line = trim($line);

        return $mapRegister->findJump($line);
    }
}
