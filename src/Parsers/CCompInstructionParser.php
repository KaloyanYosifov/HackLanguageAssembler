<?php

namespace HackAssembler\Parsers;

use HackAssembler\MapRegister;
use HackAssembler\AssemblerConstants;

class CCompInstructionParser
{
    public function handle(string $line, MapRegister $mapRegister): string
    {
        if (str_starts_with($line, '@')) {
            return '';
        }

        $destinationBits = '000';
        $controlBits = '000000';

        if (str_contains($line, ';')) {
            $line = explode(';', $line)[0];
        }

        $expression = [];

        if (str_contains('=', $line)) {
            $explodedInstruction = explode('=', $line);
            $assignedTo =  $explodedInstruction[0];
            $expression =  $explodedInstruction[1];

            $destinationBits = $mapRegister->findDestination($assignedTo);
        } else {
            $expression = $line;
        }

        return $destinationBits;
    }
}
