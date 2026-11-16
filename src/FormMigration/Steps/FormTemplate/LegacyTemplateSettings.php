<?php

namespace Give\FormMigration\Steps\FormTemplate;

use Give\FormMigration\Concerns\Blocks\BlockFactory;;
use Give\FormMigration\Contracts\FormMigrationStep;

class LegacyTemplateSettings extends FormMigrationStep
{
    public function canHandle(): bool
    {
        return 'legacy' === $this->formV2->getFormTemplate();
    }

    /**
     * @unreleased Default missing template settings to avoid undefined array key warnings.
     */
    public function process()
    {
        [
            'display_settings' => $displaySettings,
        ] = wp_parse_args($this->formV2->getFormTemplateSettings(), [
            'display_settings' => [],
        ]);

        $this->displaySettings($displaySettings);
    }

    /**
     * @unreleased Default missing display settings to avoid undefined array key warnings.
     */
    protected function displaySettings($settings)
    {
        $settings = wp_parse_args((array)$settings, [
            'display_style' => 'buttons',
            'payment_display' => 'onpage',
            'reveal_label' => '',
            'checkout_label' => __('Donate Now', 'give'),
            'form_floating_labels' => 'global',
            'display_content' => 'disabled',
            'content_placement' => 'give_pre_form',
            'form_content' => '',
        ]);

        [
            'display_style' => $displayStyle, // 'buttons',
            'payment_display' => $paymentDisplay, // 'onpage',
            'reveal_label' => $revealLabel, // '',
            'checkout_label' => $checkoutLabel, // 'Donate Now',
            'form_floating_labels' => $floatingLabels, // 'global',
            'display_content' => $displayContent, // 'disabled',
            'content_placement' => $contentPlacement, // 'give_pre_form',
            'form_content' => $formContent, // '',
        ] = $settings;

        // @note `display_style`` is not supported in v3 forms (defers to the Form Design).

        // @note `payment_display`, `reveal_label` are not supported in v3 forms (defers to the Form Design).

        // @note `checkout_label` is not supported in v3 forms (defers to the Form Design).

        // @note `form_floating_labels` is not supported in v3 forms (defers to the Form Design).

        if(give_is_setting_enabled($displayContent)) {

            $formContentSection = BlockFactory::section();
            $formContentSection->innerBlocks->append(BlockFactory::paragraph($formContent));

            if('give_pre_form' === $contentPlacement) {
                $this->fieldBlocks->prepend($formContentSection);
            } else {
                $this->fieldBlocks->append($formContentSection);
            }
        }
    }
}
