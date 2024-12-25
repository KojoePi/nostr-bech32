<?php
class Bech32 {
    const CHARSET = 'qpzry9x8gf5dnbhuvgctwj3m4e6res7l';
    const CHARSET_KEY = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        15, -1, 10, 17, 21, 20, 26, 30,  7,  5, -1, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
         1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
         1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1
    ];
    const GENERATOR = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];

    // Decode-Funktion (unverändert lassen, da sie funktioniert)
    public static function decode($str) {
        if (!str_starts_with($str, 'npub1')) {
            throw new Exception("Invalid npub prefix");
        }

        $str = substr($str, 5);
        $decoded = [];
        
        for ($i = 0; $i < strlen($str); $i++) {
            $c = ord($str[$i]);
            if ($c < 33 || $c > 126) {
                throw new Exception('Invalid character');
            }
            $value = self::CHARSET_KEY[$c];
            if ($value === -1) {
                throw new Exception('Invalid character');
            }
            $decoded[] = $value;
        }

        return self::convertBits(array_slice($decoded, 0, -6), count($decoded) - 6, 5, 8, false);
    }

    // Die neue encode-Funktion
    public static function encode($hex) {
        try {
            // Debug logging
            error_log("Starting encode with hex: " . $hex);

            // Validate hex
            if (!preg_match('/^[0-9a-f]{64}$/i', $hex)) {
                throw new Exception("Invalid hex format");
            }

            // Convert hex to bytes
            $data = array_values(unpack('C*', hex2bin($hex)));
            
            // Convert to 5-bit array
            $converted = [];
            $acc = 0;
            $bits = 0;
            
            foreach ($data as $value) {
                $acc = ($acc << 8) | $value;
                $bits += 8;
                while ($bits >= 5) {
                    $bits -= 5;
                    $converted[] = ($acc >> $bits) & 31;
                }
            }
            
            if ($bits > 0) {
                $converted[] = ($acc << (5 - $bits)) & 31;
            }

            // Convert to characters
            $ret = '';
            foreach ($converted as $v) {
                $ret .= self::CHARSET[$v];
            }

            error_log("Encoded result: " . $ret);
            return $ret;

        } catch (Exception $e) {
            error_log("Encoding error: " . $e->getMessage());
            throw $e;
        }
    }

    // Helper function für convertBits (wird von decode verwendet)
    private static function convertBits($data, $inLen, $fromBits, $toBits, $pad) {
        $acc = 0;
        $bits = 0;
        $ret = [];
        $maxv = (1 << $toBits) - 1;
        $maxacc = (1 << ($fromBits + $toBits - 1)) - 1;

        for ($i = 0; $i < $inLen; $i++) {
            $value = $data[$i];
            if ($value < 0 || ($value >> $fromBits) !== 0) {
                throw new Exception('Invalid value');
            }
            $acc = (($acc << $fromBits) | $value) & $maxacc;
            $bits += $fromBits;
            while ($bits >= $toBits) {
                $bits -= $toBits;
                $ret[] = (($acc >> $bits) & $maxv);
            }
        }

        if ($pad) {
            if ($bits > 0) {
                $ret[] = (($acc << ($toBits - $bits)) & $maxv);
            }
        } else if ($bits >= $fromBits || ((($acc << ($toBits - $bits)) & $maxv) !== 0)) {
            throw new Exception('Invalid padding');
        }

        return bin2hex(pack('C*', ...$ret));
    }
}

// Helper functions
function bech32_decode($str) {
    return Bech32::decode($str);
}

function bech32_encode($hrp, $data) {
    return Bech32::encode($data);
}
?>