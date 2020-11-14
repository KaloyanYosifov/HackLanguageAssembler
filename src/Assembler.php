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

    public function handle(string $file, bool $toFile = true): string
    {
        $this->init();

        $this->registerVariablesAndLabels($file);
        $code = $this->convertToBinary($file);

        if ($toFile) {
            file_put_contents('out.hack', $code);
        }

        return $toFile ? '' : $code;
    }

    protected function convertToBinary(string $file): string
    {

        $code = '';
        $this->scanEveryLine($file, function(string $line) use(&$code, &$lineNumber) {
            $line = $this->formatCodeLine($line);

            if (!$line) {
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
        $this->scanEveryLine($file, function(string $line) use(&$lineNumber) {
            $line = $this->formatCodeLine($line);

            if (!$line) {
                return;
            }

            if (str_starts_with('(', $line)) {
                $this->mapRegister->registerSymbol(
                    preg_replace('~[()]~', '', $line),
                    $lineNumber
                );
            } elseif (str_starts_with($line, '@')) {
                $line = str_replace('@', '', $line);
                $foundSymbol = $this->mapRegister->findSymbol($line);

                if (!$foundSymbol && !is_numeric($line)) {
                    $this->mapRegister->registerSymbol($line);
                }
            }

            $lineNumber++;
        });
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
