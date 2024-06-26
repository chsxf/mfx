<?php

use chsxf\MFX\FileTools;
use PHPUnit\Framework\TestCase;

final class FileToolsTest extends TestCase
{
    public function testMimeTypeFromExtension(): void
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
                $this->expectException(ErrorException::class);
                FileTools::mimeTypeFromExtension($parameter);
            }
        }
    }
}
