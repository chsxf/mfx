<?php

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\FileTools;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileToolsTest extends TestCase
{
    #[Test]
    public function mimeTypeFromExtension(): void
    {
        $parametersAndExpectedResults = [
            'zip' => 'application/zip',
            'ZIP' => 'application/zip',
            'pdf' => 'application/pdf',
            'PDF' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'apk' => 'application/vnd.android.package-archive',
            'ext' => NULL
        ];

        foreach ($parametersAndExpectedResults as $parameter => $expectedResult) {
            if (!empty($expectedResult)) {
                $this->assertSame($expectedResult, FileTools::mimeTypeFromExtension($parameter));
            } else {
                $this->expectException(MFXException::class);
                FileTools::mimeTypeFromExtension($parameter);
            }
        }
    }
}
