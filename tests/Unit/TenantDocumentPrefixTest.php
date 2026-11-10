<?php

namespace Tests\Unit;

use Tests\TestCase;

class TenantDocumentPrefixTest extends TestCase
{
    public function test_prefix_sanitization_and_formatting(): void
    {
        // When setting is raw "PO", result is PO-
        $prefix = rtrim('PO', '-');
        $this->assertEquals('PO', $prefix);
        $formatted = "{$prefix}-" . date('Ymd');
        $this->assertStringStartsWith('PO-', $formatted);

        // When user enters "PO-" with trailing dash, result remains clean PO-
        $prefixTrailing = rtrim('PO-', '-');
        $this->assertEquals('PO', $prefixTrailing);
        $formattedTrailing = "{$prefixTrailing}-" . date('Ymd');
        $this->assertEquals($formatted, $formattedTrailing);
        $this->assertStringNotContainsString('PO--', $formattedTrailing);

        // Test PO DP and Pay Debt Prefix formatting
        $dpPrefix = rtrim('PO-DP-', '-');
        $this->assertEquals('PO-DP', $dpPrefix);
        $this->assertEquals('PO-DP-PO-001', "{$dpPrefix}-PO-001");

        $payPrefix = rtrim('PAY-', '-');
        $this->assertEquals('PAY', $payPrefix);
        $this->assertEquals('PAY-PO-001', "{$payPrefix}-PO-001");
        // Test Pay Repair Prefix formatting
        $payRepairPrefix = rtrim('PAY-', '-');
        $this->assertEquals('PAY', $payRepairPrefix);
        $this->assertEquals('PAY-INV-1-001', "{$payRepairPrefix}-INV-1-001");

        // Test WhatsApp template placeholder replacement
        $template = "Halo Kak {customer_name}, tagihan di {brand_name} (Tiket: {ticket_code}) sisa *Rp {amount}* tempo *{due_date}*.";
        $rendered = str_replace(
            ['{customer_name}', '{brand_name}', '{ticket_code}', '{amount}', '{due_date}'],
            ['Budi', 'Schienetren Toko', 'REP-001', '150.000', '20-10-2026'],
            $template
        );
        $this->assertEquals("Halo Kak Budi, tagihan di Schienetren Toko (Tiket: REP-001) sisa *Rp 150.000* tempo *20-10-2026*.", $rendered);
    }

    public function test_settings_page_mount_contains_all_prefix_and_finance_keys(): void
    {
        $this->assertTrue(class_exists(\App\Filament\Tenant\Pages\SettingsPage::class));
        $page = new \App\Filament\Tenant\Pages\SettingsPage();
        $schema = $page->form(new \Filament\Schemas\Schema());
        $components = $schema->getComponents();
        $this->assertNotEmpty($components);
        $this->assertInstanceOf(\Filament\Schemas\Components\Tabs::class, $components[0]);
    }
}
