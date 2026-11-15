# Voltcraft Android app (NativePHP)

This project’s Android app opens the hosted Voltcraft ERP in a NativePHP WebView. The site must be reachable over HTTPS; the app does not run the web server on the phone.

## What is in this project

- `app/NativeComponents/ErpWebView.php` and `resources/views/native/erp-web-view.blade.php` define the WebView.
- `routes/mobile.php` registers the mobile route; the build starts at `/erp`.
- `public/icon.png` is the Android/iOS app icon. Replace it with a square PNG of at least 1024 × 1024 pixels to change the icon.
- `public/splash.png` and `public/splash-dark.png` are the light and dark splash screens. Keep each image at a **2:3 width-to-height ratio** (the current images are 1080 × 1620) to prevent stretching on Android. The splash screens intentionally do not show the app icon.
- NativePHP and its UI package are Composer dependencies. Android resources and the APK are generated under the ignored `nativephp/` directory.

## Change the URL, icon, or splash screen

### Change the website URL

Edit the `src` attribute in `resources/views/native/erp-web-view.blade.php`. It currently points to:

```html
src="https://alihams-com.stackstaging.com/voltcraft/public/"
```

Replace it with the HTTPS URL the app should open. This is the **website URL**. Separately, `routes/mobile.php` maps the app's local `/erp` route to the WebView, and `--start-url=/erp` tells NativePHP to open that route at launch. Keep `/erp` unless you also change the mobile route and build command.

### Change the app icon

Replace `public/icon.png` with your icon. Use a square PNG at least 1024 × 1024 pixels. NativePHP generates Android launcher icon sizes from this source when you build.

### Change the splash screen

Replace `public/splash.png` (light appearance) and `public/splash-dark.png` (dark appearance). Use the same dimensions and a **2:3 width-to-height ratio**, such as 1080 × 1620. Keep all desired text and artwork inside the image; the app icon is not overlaid on these splash screens.

After changing any of these, rebuild and reinstall with:

```powershell
php artisan native:run android --start-url=/erp --no-tty
```

### Where the configuration lives

- `resources/views/native/erp-web-view.blade.php`: hosted website URL and WebView options.
- `routes/mobile.php`: local app route (`/erp`) and the component it opens.
- `config/nativephp.php`: NativePHP settings, including the default `start_url`, Android SDK/build settings, and Android image output format. The icon and splash source files are in `public/`, not this config.
- `.env`: optional NativePHP overrides, such as `NATIVEPHP_APP_ID` and `NATIVEPHP_START_URL`. The build command's `--start-url=/erp` explicitly selects the launch route for this Android build.
- The dashboard top bar uses NativePHP's injected `--inset-top` safe-area value so its logo and sign-out button sit below the phone status icons. Because the WebView opens the hosted site URL, deploy changes to the dashboard Blade views to that web server; a native APK rebuild is not needed for remote website updates.

## Windows prerequisites

Install PHP 8.3+, Composer, JDK 17, Android Studio with the Android SDK and Platform Tools, and 7-Zip. Enable PHP's ZIP extension in the `php.ini` used by the command line.

From PowerShell at the project root, check PHP and ZIP:

```powershell
php -v
composer -V
php --ini
php -m | findstr /I zip
```

If `zip` is missing, enable `extension=zip` in the loaded `php.ini`, then reopen the terminal and check again.

Set the Android environment for the current PowerShell session (adjust `JAVA_HOME` if JDK 17 is installed elsewhere):

```powershell
$env:JAVA_HOME = 'C:\Program Files\Eclipse Adoptium\jdk-17.0.21.9-hotspot'
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
$env:PATH = "$env:JAVA_HOME\bin;$env:ANDROID_HOME\platform-tools;$env:PATH"
java -version
& "$env:ANDROID_HOME\platform-tools\adb.exe" devices
```

Unlock the phone, enable USB debugging, connect it, and accept its USB debugging prompt. `adb devices` should show the device as `device`, not `unauthorized`.

## Install and build

Run these from the project root. If preparing a fresh checkout, install its dependencies first:

```powershell
composer install
```

The Android project was initialized with:

```powershell
php artisan native:install android --force --no-interaction
```

**Use that install command only when initializing or intentionally regenerating the NativePHP Android project:** `--force` can replace the generated `nativephp/android` directory.

Build, install, and launch the app on the connected device:

```powershell
php artisan native:run android --start-url=/erp --no-tty
```

The debug APK is written to:

```text
nativephp\android\app\build\outputs\apk\debug\app-debug.apk
```

To confirm the device connection or install the APK manually:

```powershell
& "$env:ANDROID_HOME\platform-tools\adb.exe" devices
& "$env:ANDROID_HOME\platform-tools\adb.exe" install -r "nativephp\android\app\build\outputs\apk\debug\app-debug.apk"
```

After changing the icon or splash images, run the build command again so NativePHP copies the updated assets into the APK.
