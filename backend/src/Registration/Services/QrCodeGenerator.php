<?php

declare(strict_types=1);

namespace App\Registration\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

final class QrCodeGenerator
{
    public function generateDataUri(string $data): string
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 16,
            foregroundColor: new Color(16, 33, 60),
            backgroundColor: new Color(255, 255, 255),
        );

        return 'data:image/png;base64,' . base64_encode((new PngWriter())->write($qrCode)->getString());
    }
}
