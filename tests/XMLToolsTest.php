<?php

use chsxf\MFX\XMLTools;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class XMLToolsTest extends TestCase
{
	#[Test]
	public function build(): void
	{
		$srcData = ['b' => 'true', 'i' => '10', 'f' => '12.34', 's' => 'test string'];

		$builtValue = XMLTools::build($srcData);

		$expectedValue = '<?xml version="1.0" encoding="UTF-8"?>
<root>
	<array>
		<key><![CDATA[b]]></key>
		<bool>true</bool>
		<key><![CDATA[i]]></key>
		<int>10</int>
		<key><![CDATA[f]]></key>
		<float>12.34</float>
		<key><![CDATA[s]]></key>
		<string><![CDATA[test string]]></string>
	</array>
</root>
';

		$this->assertSame($expectedValue, $builtValue);
	}
}
