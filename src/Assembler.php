<?php

namespace HackAssembler;

use HackAssembler\Parsers\AInstructionParser;
use HackAssembler\Parsers\CCompInstructionParser;
use HackAssembler\Parsers\CJumpInstructionParser;

class Assembler
{
    protected MapRegister $mapRegister;
    protected array $cInstructionParsers = [];

    public function __construct()
    {
        $this->mapRegister = new MapRegister();
    }

    public function handle(string $file, ?string $outputFile = null, bool $toFile = true): string
    {
        $this->init();

        $this->registerVariablesAndLabels($file);
        $code = $this->convertToBinary($file);

        if ($toFile) {
            file_put_contents($outputFile ?? sprintf('%s.%s', pathinfo($file, PATHINFO_FILENAME), 'hack'), $code);
        }

        return $toFile ? '' : $code;
    }

    protected function convertToBinary(string $file): string
    {

        $code = '';
        $this->scanEveryLine($file, function(string $line) use(&$code) {
            $line = $this->formatCodeLine($line);

            if (!$line || str_starts_with($line, '(')) {
                return;
            }

            if (str_starts_with($line, '@')) {
                $aInstructionParser = new AInstructionParser();
                $line = $aInstructionParser->handle($line, $this->mapRegister);
                $instructionBit = AssemblerConstants::A_INSTRUCTION;
            } else {
                $instructionBit = AssemblerConstants::C_INSTRUCTION;

                $lineInBinaryCode = '';
                foreach ($this->cInstructionParsers as $parser) {
                    $lineInBinaryCode .= (new $parser)->handle($line, $this->mapRegister);
                }

                $line = $lineInBinaryCode;
            }

            if (strlen($line) > AssemblerConstants::MAX_BITS_FOR_NUMBER) {
                $line = substr($line, 0, strlen($line) - AssemblerConstants::MAX_BITS_FOR_NUMBER);
            }

            $code .= $instructionBit . $line . PHP_EOL;
        });

        return $code;
    }

    protected function registerVariablesAndLabels(string $file): void
    {
        $lineNumber = 0;
        $labelsFound = [];
        $symbolsFound = [];
        $this->scanEveryLine($file, function(string $line) use(&$lineNumber, &$labelsFound, &$symbolsFound) {
            $line = $this->formatCodeLine($line);

            if (!$line) {
                return;
            }

            if (str_starts_with($line, '(')) {
                $labelsFound[preg_replace('~[()]~', '', $line)] = $lineNumber;

                return;
            } elseif (str_starts_with($line, '@')) {
                $line = str_replace('@', '', $line);
                $foundSymbol = $this->mapRegister->findSymbol($line);

                if (is_null($foundSymbol) && !is_numeric($line)) {
                    $symbolsFound[] = $line;
                }
            }

            $lineNumber++;
        });

        foreach ($labelsFound as $label => $lineNumber) {
            $this->mapRegister->registerSymbol($label, $lineNumber);
        }

        foreach ($symbolsFound as $symbol) {
            if (array_key_exists($symbol, $labelsFound)) {
                continue;
            }

            $this->mapRegister->registerSymbol($symbol);
        }
    }

    protected function formatCodeLine(string $line): string
    {
        // remove comments from line
        if (($foundCommentPos = strpos($line, '//')) !== false) {
            $line = substr_replace($line, '', $foundCommentPos);
        }

        return trim($line);
    }

    protected function scanEveryLine(string $file, callable $callback) {
        if (!file_exists($file)) {
            throw new \LogicException('Assembly file doesn\'t exist!');
        }

        if (!preg_match('~\.asm$~', $file)) {
            throw new \LogicException('File is not with the assembly extension!');
        }

        $file = fopen($file, 'r');

        if (!$file) {
            throw new \LogicException('Couldn\'t open file!');
        }


        while (($line = fgets($file, 4096)) !== false) {
            $callback($line);
        }

        if (!feof($file)) {
            throw new \LogicException('Something went wrong while reading the file.');
        }

        fclose($file);

        return '';
    }

    protected function init(): void
    {
        $this->mapRegister->init();
        $this->cInstructionParsers = [
            CCompInstructionParser::class,
            CJumpInstructionParser::class,
        ];
    }
}
