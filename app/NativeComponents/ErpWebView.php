<?php

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class ErpWebView extends NativeComponent
{
    public function render(): View
    {
        return view('native.erp-web-view');
    }
}
