<?php

use App\NativeComponents\ErpWebView;
use Illuminate\Support\Facades\Route;

Route::native('/erp', ErpWebView::class);
