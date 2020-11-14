<?php

namespace HackAssembler;

class MapRegister
{
    private const INITIAL_MEMORY_LABELS_COUNT = 15;

    protected array $jumpMap = [];
    protected array $symbolsMap = [];
    protected array $destinationMap = [];
    protected array $aluControlBits = [];
    protected int $newVariablesMemoryStart = 16;

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

        $this->aluControlBits = [
            '0' => '101010',
            '1' => '111111',
            '-1' => '111010',
            'D' => '001100',
            'M' => '001100',
            'A' => '110000',
            '!D' => '001101',
            '!A' => '110001',
            '!M' => '110001',
            '-D' => '001111',
            '-A' => '110011',
            '-M' => '110011',
            'D+1' => '011111',
            'A+1' => '110111',
            'M+1' => '110111',
            'D-1' => '001110',
            'A-1' => '110010',
            'M-1' => '110010',
            'D+A' => '000010',
            'M+D' => '000010',
            'D+M' => '000010',
            'D-A' => '010011',
            'D-M' => '010011',
            'A-D' => '000111',
            'M-D' => '000111',
            'A&D' => '000000',
            'D&A' => '000000',
            'M&D' => '000000',
            'D&M' => '000000',
            'D|A' => '010101',
            'A|D' => '010101',
            'M|D' => '010101',
            'D|M' => '010101',
        ];

        return $this;
    }

    public function registerSymbol(string $name): self
    {
        if (!$this->findSymbol($name)) {
            $this->symbolsMap[$name] = $this->newVariablesMemoryStart++;
        }

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

    public function findAluControlBits(string $instruction): string
    {
        if (!array_key_exists($instruction, $this->aluControlBits)) {
            return '000000';
        }

        return $this->aluControlBits[$instruction];
    }
}
