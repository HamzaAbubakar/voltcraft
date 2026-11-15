<?php

namespace Tests\Feature\NativeComponents;

use Native\Mobile\Testing\Native;
use Tests\TestCase;

class ErpWebViewTest extends TestCase
{
    public function test_erp_route_renders_remote_site_in_fullscreen_webview(): void
    {
        Native::visit('/erp')
            ->assertElement('webview', fn (array $node): bool => ($node['props']['src'] ?? null) === 'https://alihams-com.stackstaging.com/voltcraft/public/'
                && ($node['props']['javascript'] ?? false) === true
                && ($node['props']['dom_storage'] ?? false) === true
                && ($node['props']['fullscreen'] ?? false) === true);
    }
}
