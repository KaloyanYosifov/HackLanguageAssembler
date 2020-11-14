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
        $instructionBit = AssemblerConstants::A_INSTRUCTION;

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
        $this->mapRegister->init();
        $this->cInstructionParsers = [
            CCompInstructionParser::class,
            CJumpInstructionParser::class,
        ];
    }
}
