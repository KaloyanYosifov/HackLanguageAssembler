<?php

namespace HackAssembler;

class MapRegister
{
    private const INITIAL_MEMORY_LABELS_COUNT = 15;

    protected array $jumpMap = [];
    protected array $symbolsMap = [];
    protected array $destinationMap = [];
    protected array $aluControlBits = [];

    public function init(): self
    {
        $this->symbolsMap = [
            'SP' => 0,
            'LCL' => 1,
            'ARG' => 2,
            'THIS' => 3,
            'THAT' => 4,
            'SCREEN' => 16384,
            'KBD' => 24576,
        ];

        for ($i = 0; $i < static::INITIAL_MEMORY_LABELS_COUNT; $i++) {
            $this->symbolsMap['R' . $i] = $i;
        }

        $this->jumpMap = [
            'JGT' => '001',
            'JGE' => '011',
            'JLT' => '100',
            'JNE' => '101',
            'JLE' => '110',
            'JMP' => '111',
        ];

        $this->destinationMap = [
            'M' => '001',
            'D' => '010',
            'MD' => '011',
            'A' => '100',
            'AM' => '101',
            'AD' => '110',
            'AMD' => '111',
        ];

        return $this;
    }

    public function findSymbol(string $symbolName): string
    {
        if (!array_key_exists($symbolName, $this->symbolsMap)) {
            return '';
        }

        return $this->symbolsMap[$symbolName];
    }

    public function findDestination(string $destionationName): string
    {
        if (!array_key_exists($destionationName, $this->destinationMap)) {
            return '000';
        }

        return $this->destinationMap[$destionationName];
    }

    public function findJump(string $jumpName): string
    {
        if (!array_key_exists($jumpName, $this->jumpMap)) {
            return '000';
        }

        return $this->jumpMap[$jumpName];
    }

    public function findAluControlBit(string $instruction): string
    {
        if (!array_key_exists($instruction, $this->aluControlBits)) {
            return '000';
        }

        return $this->aluControlBits[$instruction];
    }
}
