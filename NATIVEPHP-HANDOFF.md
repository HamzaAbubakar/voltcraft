# Voltcraft NativePHP mobile app handoff

## Project and goal

This is the Laravel 13 Voltcraft ERP project at `D:\laragon\www\voltcraft`. The Android app is a NativePHP Mobile shell that opens the **hosted ERP website** in a fullscreen WebView. Laravel/PHP and the ERP data continue to run on the web server; the app is not a standalone/offline copy of the site.

The user wants to continue building and testing this Android app, keeping NativePHP changes identifiable separately from web ERP changes.

## Current repository state

- Latest known commit: `9bee0bb` (`Enhance dashboard layout for mobile by adjusting safe area insets and viewport settings`), on `main` and `origin/main`.
- NativePHP implementation commit: `a121828` (`Add NativePHP Android app setup`).
- Last checked worktree was clean.
- `NATIVEPHP-ANDROID.md` is the project guide for setup, changing the website URL/icon/splash screen, and relevant commands.
- Android project and APK output are generated under ignored `nativephp/`; the committed source assets/config are in the paths below.

## Important files

- `app/NativeComponents/ErpWebView.php`: NativePHP screen component.
- `resources/views/native/erp-web-view.blade.php`: remote website URL and WebView properties.
- `routes/mobile.php`: maps the local app route `/erp`.
- `app/Providers/NativeServiceProvider.php`: registers `nativephp/mobile-ui`.
- `config/nativephp.php`: NativePHP build/runtime configuration.
- `public/icon.png`: supplied Voltcraft charging-station image used for launcher icon (1024 × 1024).
- `public/splash.png`, `public/splash-dark.png`: splash artwork (1080 × 1620, 2:3 ratio); intentionally has no separate icon, per user request.
- `tests/Feature/NativeComponents/ErpWebViewTest.php`: checks remote URL, JS/DOM storage, and fullscreen WebView props.
- `NATIVEPHP-ANDROID.md`: detailed Windows setup/build instructions.

The WebView currently loads `https://alihams-com.stackstaging.com/voltcraft/public/`. To change the **website** URL, change `src` in `resources/views/native/erp-web-view.blade.php`. `/erp` is a separate local NativePHP route and is selected as the launch route in the build command.

## Mobile safe area

The user reported the hosted dashboard logo and sign-out controls overlapped the phone status bar. The web layout now uses NativePHP's injected `--inset-top` and CSS `env(safe-area-inset-top)` with `viewport-fit=cover`; its top bar and mobile sidebar/overlay account for the inset. This is a **hosted website change** in `resources/views/layouts/dashboard.blade.php`, not an APK-only change. The change is on `9bee0bb`, alongside responsive dashboard updates. Deploy the changed web files to the hosted site for the phone to receive them.

## Windows Android environment

Previously confirmed on the user's Windows machine:

- Temurin JDK 17.0.21 at `C:\Program Files\Eclipse Adoptium\jdk-17.0.21.9-hotspot`.
- Android SDK at `%LOCALAPPDATA%\Android\Sdk`.
- 7-Zip installed.
- PHP CLI needs `extension=zip` enabled for NativePHP install.
- Pixel 7 was previously authorized in ADB (serial `28111FDH2006JW`); reconfirm with `adb devices` when building.
- Android Gradle 8.14.5 build worked with JDK 17; Java 25 was incompatible in an earlier attempt.

For each new PowerShell session, configure its environment (adjust the JDK path if needed):

```powershell
$env:JAVA_HOME = 'C:\Program Files\Eclipse Adoptium\jdk-17.0.21.9-hotspot'
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
$env:PATH = "$env:JAVA_HOME\bin;$env:ANDROID_HOME\platform-tools;$env:PATH"
java -version
& "$env:ANDROID_HOME\platform-tools\adb.exe" devices
```

From the project root:

```powershell
composer install
php artisan native:run android --start-url=/erp --no-tty
```

The last command builds a debug APK, installs it, and launches it if an authorized device is connected. APK location:

```text
nativephp\android\app\build\outputs\apk\debug\app-debug.apk
```

The project was initialized with `php artisan native:install android --force --no-interaction`. Do not rerun with `--force` casually: it removes and regenerates the Android project directory.

## Prior verification

- `php artisan test --compact tests/Feature/NativeComponents/ErpWebViewTest.php`: passed (1 test, 3 assertions).
- NativePHP Android debug build and install/launch succeeded on the connected Pixel after the latest icon/splash edits. Subsequent commit `9bee0bb` is a remote dashboard safe-area/layout change.

## Guidance for the next agent

- First inspect `git status` and recent commits; do not assume there are no new user changes.
- Preserve unrelated ERP work. Stage/commit only explicitly requested NativePHP files when asked; keep hosted Blade/dashboard work separate when appropriate.
- For URL/icon/splash changes, follow `NATIVEPHP-ANDROID.md`. Rebuild the APK after changing app assets or NativePHP config. A change to the hosted web ERP generally needs deployment to the web server, not a new APK.
- The splash source ratio is intentionally 2:3 because NativePHP scales it into Android drawable density resources; do not replace it with a typical 9:16 screenshot or edit generated resources under ignored `nativephp/`.
