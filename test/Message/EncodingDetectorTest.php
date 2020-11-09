<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Message;

use PHPUnit\Framework\TestCase;
use Vonage\Message\EncodingDetector;

class EncodingDetectorTest extends TestCase
{
    /**
     * @dataProvider unicodeProvider
     *
     * @param $content
     * @param $expected
     */
    public function testDetectsUnicode($content, $expected): void
    {
        $d = new EncodingDetector();
        $this->assertEquals($expected, $d->requiresUnicodeEncoding($content));
    }

    public function unicodeProvider(): array
    {
        $r = [];

        $r['ascii'] = ['Hello World', false];
        $r['emoji'] = ['Testing 💪 👌', true];
        $r['danish'] = [
            'Quizdeltagerne spiste jordbær med fløde, mens cirkusklovnen Wolther spillede på xylofon.',
            false
        ];
        $r['german'] = ['Heizölrückstoßabdämpfung', false];
        $r['greek'] = ['  Γαζέες καὶ μυρτιὲς δὲν θὰ βρῶ πιὰ στὸ χρυσαφὶ ξέφωτο', true];
        $r['spanish'] = [
            'El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.',
            true
        ];
        $r['frenchWithUnicode'] = [
            'Le cœur déçu mais l\'âme plutôt naïve, Louÿs rêva de crapaüter en canoë au delà des îles, ' .
            'près du mälström où brûlent les novæ.',
            true
        ];
        $r['frenchWithOnlyGSM'] = [
            'j\'étais donc plein de songes ! L\'espérance en chantant me berçait de mensonges. J\'étais ' .
            'donc cet enfant, hélas !',
            false
        ];
        $r['icelandic'] = ['Kæmi ný öxi hér ykist þjófum nú bæði víl og ádrepa ', true];
        $r['japanese-hiragana'] = ['いろはにほへとちりぬるを', true];
        $r['japanese-katakana'] = ['イロハニホヘト チリヌルヲ ワカヨタレソ ツネナラム', true];
        $r['hebrew'] = ['  ? דג סקרן שט בים מאוכזב ולפתע מצא לו חברה איך הקליטה', true];
        $r['polish'] = ['Pchnąć w tę łódź jeża lub ośm skrzyń fig', true];
        $r['russian'] = ['В чащах юга жил бы цитрус? Да, но фальшивый экземпляр!', true];
        $r['thai'] = ['กว่าบรรดาฝูงสัตว์เดรัจฉาน', true];
        $r['turkish'] = ['Pijamalı hasta, yağız şoföre çabucak güvendi.', true];
        $r['LF'] = ["\n", false];
        $r['CR'] = ["\r", false];

        return $r;
    }
}
