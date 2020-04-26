<?php

class WSFrameFragment
{
    public string $data;

    public static function fromStream($stream): WSFrameFragment
    {
        $frame = new self;

        $byte1 = fgetc($stream);
        // TODO: mask flag ... final flag .. opcode flag
        $byte2 = fgetc($stream);

        $length = ord($byte2) & 127;
        if ($length == 126) {
            $length = unpack('nu16', fread($stream, 2))['u16'];
        } else if ($length == 127) {
            $length = unpack('Ju64', fread($stream, 8))['u64'];
        }

        $masking_key = fread($stream, 4);
        $data = fread($stream, $length);

        $frame->data = $data ^ str_pad('', $length, $masking_key, STR_PAD_RIGHT);

        return $frame;
    }

    public static function encode($text): string
    {
        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if($length <= 125)      $header = pack('CC', $b1, $length);     elseif($length > 125 && $length < 65536)        $header = pack('CCS', $b1, 126, $length);   elseif($length >= 65536)
            $header = pack('CCN', $b1, 127, $length);

        return $header.$text;
    }

    public function __toString(): string
    {
        return $this->data;
    }
}
