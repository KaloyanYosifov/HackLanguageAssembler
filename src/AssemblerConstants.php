<?php
declare(strict_types=1);

namespace HackAssembler;

class AssemblerConstants
{
    public const A_INSTRUCTION = '0';
    public const C_INSTRUCTION = '111';
    public const MAX_BITS_FOR_NUMBER = 15;

    public static function convertNumberToBinary(int $number)
    {
        return str_pad(decbin($number), AssemblerConstants::MAX_BITS_FOR_NUMBER, '0', STR_PAD_LEFT);
    }
}
