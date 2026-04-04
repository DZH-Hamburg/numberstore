<?php

namespace App\Support;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

final class TotpProvisioningQr
{
    /**
     * Inline-SVG für otpauth://-URI (kein externer Request – geeignet für CSP / eingeschränkte Browser).
     */
    public static function svg(string $otpAuthUri): string
    {
        $qrCode = new QrCode(data: $otpAuthUri, size: 200, margin: 8);
        $writer = new SvgWriter;

        return $writer->write($qrCode, options: [
            SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
        ])->getString();
    }
}
