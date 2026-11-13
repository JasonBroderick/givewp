<?php

namespace Give\Tests\Unit\Admin;

use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use RuntimeException;

/**
 * The note HTML returned by the add-note AJAX handler must carry the donation ID, so the
 * new note's Delete link works before the page is reloaded (SMTNC-3256).
 *
 * @unreleased
 *
 * @covers ::give_ajax_store_payment_note
 */
final class StorePaymentNoteAjaxTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_ajax_store_payment_note')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/actions.php';
        }

        add_filter('wp_doing_ajax', '__return_true');
    }

    /**
     * @unreleased
     */
    public function tearDown(): void
    {
        remove_filter('wp_doing_ajax', '__return_true');
        remove_all_filters('pre_kses');
        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @unreleased
     */
    public function testNewNoteHtmlHasTheDonationIdInItsDeleteLink(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $donation = Donation::factory()->create();

        $_POST = [
            'payment_id' => $donation->id,
            'note' => 'A note',
        ];
        $_REQUEST = $_POST + ['_ajax_nonce' => wp_create_nonce('give_insert_payment_note')];

        $html = $this->runHandler();

        $this->assertStringContainsString('data-payment-id="' . $donation->id . '"', $html);
        $this->assertStringContainsString('payment_id=' . $donation->id, $html);
    }

    /**
     * The handler ends with die(), so the note HTML is caught as it is passed to wp_kses_post(),
     * the last call before die().
     *
     * @unreleased
     */
    private function runHandler(): string
    {
        add_filter('pre_kses', static function ($content) {
            // The handler also runs the note text through wp_kses(); only stop on the note HTML.
            if (is_string($content) && false !== strpos($content, 'give-payment-note')) {
                throw new RuntimeException($content);
            }

            return $content;
        });

        try {
            give_ajax_store_payment_note();
        } catch (RuntimeException $exception) {
            return $exception->getMessage();
        }

        $this->fail('The handler did not output the note HTML.');
    }
}
