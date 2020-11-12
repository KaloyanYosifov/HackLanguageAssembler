<?php

namespace HackAssembler;

class Assembler
{
    private const MAX_BITS_FOR_NUMBER = 15;
    private const INITIAL_MEMORY_LABELS_COUNT = 15;
    protected array $jumpMap = [];
    protected array $symbolsMap = [];
    protected array $destinationMap = [];

    public function handle(string $file): string
    {
        if (!file_exists($file)) {
            throw new \LogicException('Assembly file doesn\'t exist!');
        }

        if (!preg_match('~\.asm$~', $file)) {
            throw new \LogicException('File is not with the assembly extension!');
        }

        $this->init();

        $file = fopen($file, 'r');

        if (!$file) {
            throw new \LogicException('Couldn\'t open file!');
        }

        $code = '';
        while (($line = fgets($file, 4096)) !== false) {
            // remove comments from line
            if (($foundCommentPos = strpos($line, '//')) !== false) {
                $line = substr_replace($line, '', $foundCommentPos);
            }

            $line = trim($line);

            if (!$line) {
                continue;
            }

            if (str_starts_with($line, '@')) {
                $line = str_replace('@', '', $line);

                if (array_key_exists($line, $this->symbolsMap)) {
                    $line = $this->symbolsMap[$line];
                }

                $binaryNumber = str_pad(decbin($line), static::MAX_BITS_FOR_NUMBER, '0', STR_PAD_LEFT);

                // remove overflow
                if (strlen($binaryNumber) > static::MAX_BITS_FOR_NUMBER) {
                    $binaryNumber = substr($binaryNumber, 0, strlen($binaryNumber) - static::MAX_BITS_FOR_NUMBER);
                }

                $code .= '0' . $binaryNumber . PHP_EOL;
            }
        }

        if (!feof($file)) {
            throw new \LogicException('Something went wrong while reading the file.');
        }

        fclose($file);

        file_put_contents('out.hack', $code);

        return '';
    }

    protected function init(): void
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
            'null' => '000',
            'JGT' => '001',
            'JGE' => '011',
            'JLT' => '100',
            'JNE' => '101',
            'JLE' => '110',
            'JMP' => '111',
        ];

        $this->destinationMap = [
            'null' => '000',
            'M' => '001',
            'D' => '010',
            'MD' => '011',
            'A' => '100',
            'AM' => '101',
            'AD' => '110',
            'AMD' => '111',
        ];
    }
}
