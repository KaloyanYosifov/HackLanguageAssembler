<?php

namespace HackAssembler\Parsers;

use HackAssembler\MapRegister;

class CCompInstructionParser
{
    public function handle(string $line, MapRegister $mapRegister): string
    {
        if (str_starts_with($line, '@')) {
            return '';
        }

        $memoryTypeBit = '0';
        $destinationBits = '000';

        if (str_contains($line, ';')) {
            $line = explode(';', $line)[0];
        }

        $line = trim($line);

        if (str_contains($line, '=')) {
            $explodedInstruction = explode('=', $line);
            $assignedTo =  $explodedInstruction[0];
            $expression =  $explodedInstruction[1];

            $destinationBits = $mapRegister->findDestination($assignedTo);
        } else {
            $expression = $line;
        }

        $controlBits = $mapRegister->findAluControlBits($expression);

        if (str_contains($expression, 'M')) {
            $memoryTypeBit = '1';
        }

        return $memoryTypeBit . $controlBits . $destinationBits;
    }
}
