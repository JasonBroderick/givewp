<?php

namespace Give\Tests\Unit\Gateways\Offline;

use Give\Tests\TestCase;

/**
 * @unreleased
 */
class DefaultOfflineDonationEmailContentTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testDefaultEmailContentKeepsQuotesUnescaped()
    {
        $content = give_get_default_offline_donation_email_content();

        $this->assertStringContainsString('"{sitename}"', $content);
        $this->assertStringContainsString('"{form_title}"', $content);
        $this->assertStringNotContainsString('&quot;', $content);
    }

    /**
     * @unreleased
     */
    public function testDefaultEmailContentKeepsApostrophesInTranslationsUnescaped()
    {
        $filter = static function ($translation, $text, $domain) {
            return 'give' === $domain && 'Thanks in advance!' === $text ? "Merci d'avance !" : $translation;
        };
        add_filter('gettext', $filter, 10, 3);

        $content = give_get_default_offline_donation_email_content();

        remove_filter('gettext', $filter, 10);

        $this->assertStringContainsString("Merci d'avance !", $content);
        $this->assertStringNotContainsString('&#039;', $content);
    }
}
