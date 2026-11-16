<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormTemplate\LegacyTemplateSettings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\FormTemplate\LegacyTemplateSettings
 */
class TestLegacyTemplateSettings extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testProcessShouldNotFailWhenTemplateSettingsAreMissing(): void
    {
        $v2Form = $this->createSimpleDonationForm(['meta' => [
            '_give_form_template' => 'legacy',
        ]]);

        $v3Form = $this->migrateForm($v2Form, LegacyTemplateSettings::class);

        $this->assertNull($v3Form->blocks->findByName('givewp/paragraph'));
    }

    /**
     * @unreleased
     */
    public function testProcessShouldUseDefaultsForPartialDisplaySettings(): void
    {
        $v2Form = $this->createSimpleDonationForm(['meta' => [
            '_give_form_template' => 'legacy',
            '_give_legacy_form_template_settings' => [
                'display_settings' => [
                    'display_content' => 'enabled',
                    'form_content' => 'Form content',
                ],
            ],
        ]]);

        $v3Form = $this->migrateForm($v2Form, LegacyTemplateSettings::class);

        $firstBlock = $v3Form->blocks->getBlocks()[0];
        $this->assertSame('givewp/section', $firstBlock->name);
        $this->assertSame('Form content', $firstBlock->innerBlocks->findByName('givewp/paragraph')->getAttribute('content'));
    }

    /**
     * @unreleased
     */
    public function testProcessShouldAppendFormContentWhenPlacedAfterForm(): void
    {
        $v2Form = $this->createSimpleDonationForm(['meta' => [
            '_give_form_template' => 'legacy',
            '_give_legacy_form_template_settings' => [
                'display_settings' => [
                    'display_style' => 'buttons',
                    'payment_display' => 'onpage',
                    'reveal_label' => '',
                    'checkout_label' => 'Donate Now',
                    'form_floating_labels' => 'global',
                    'display_content' => 'enabled',
                    'content_placement' => 'give_post_form',
                    'form_content' => 'Form content',
                ],
            ],
        ]]);

        $v3Form = $this->migrateForm($v2Form, LegacyTemplateSettings::class);

        $blocks = $v3Form->blocks->getBlocks();
        $lastBlock = end($blocks);
        $this->assertSame('Form content', $lastBlock->innerBlocks->findByName('givewp/paragraph')->getAttribute('content'));
    }
}
