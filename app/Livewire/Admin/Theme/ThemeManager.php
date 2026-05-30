<?php

namespace App\Livewire\Admin\Theme;

use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use Storage;

class ThemeManager extends Component
{
    use WithFileUploads;

    private $themes_folder;

    public $themes = [];

    // Required theme structure

    public function mount()
    {
        $this->themes_folder = config('themes.folder', resource_path('views/themes'));

        $this->installThemes();
        $this->refreshThemes();
    }

    private function refreshThemes()
    {
        $all_themes = Theme::all();
        $this->themes = [];
        foreach ($all_themes as $theme) {
            if (file_exists(resource_path('views/themes/'.$theme->folder))) {
                array_push($this->themes, $theme);
            }
        }
    }

    private function getThemesFromFolder()
    {
        $themes = [];

        if (! file_exists($this->themes_folder)) {
            mkdir($this->themes_folder);
        }

        $scandirectory = scandir($this->themes_folder);

        if (isset($scandirectory)) {
            foreach ($scandirectory as $folder) {
                $json_file = $this->themes_folder.'/'.$folder.'/theme.json';
                if (file_exists($json_file)) {
                    $themes[$folder] = json_decode(file_get_contents($json_file), true);
                    $themes[$folder]['folder'] = $folder;
                    $themes[$folder] = (object) $themes[$folder];
                }
            }
        }

        return (object) $themes;
    }

    private function installThemes()
    {
        $themes = $this->getThemesFromFolder();

        foreach ($themes as $theme) {
            if (isset($theme->folder)) {
                $theme_exists = Theme::where('folder', '=', $theme->folder)->first();
                $themeImagePath = 'themes/'.$theme->folder.'/theme.jpg';

                if (file_exists(resource_path('views/themes/'.$theme->folder.'/theme.jpg'))) {
                    Storage::disk('public')->put($themeImagePath, file_get_contents(resource_path('views/themes/'.$theme->folder.'/theme.jpg')));
                }

                // If the theme does not exist in the database, then update it.
                if (! isset($theme_exists->id)) {
                    $version = isset($theme->version) ? $theme->version : '';
                    Theme::create(['name' => $theme->name, 'folder' => $theme->folder, 'version' => $version, 'theme_url' => $themeImagePath, 'active' => 1]);
                    if (config('themes.publish_assets', true)) {
                        $this->publishAssets($theme->folder);
                    }
                } else {
                    // If it does exist, let's make sure it's been updated
                    $theme_exists->name = $theme->name;
                    $theme_exists->version = isset($theme->version) ? $theme->version : '';
                    $theme_exists->theme_url = $themeImagePath;
                    $theme_exists->active = 1;
                    $theme_exists->save();
                    if (config('themes.publish_assets', true)) {
                        $this->publishAssets($theme->folder);
                    }
                }
            }
        }
    }

    public function activate($theme_folder)
    {
        $theme = Theme::where('folder', '=', $theme_folder)->first();

        if (isset($theme->id)) {
            $this->deactivateThemes();
            $theme->active = 1;
            $theme->save();
            $this->writeThemeJson($theme_folder);
            // Replace welcome.blade.php with the marketing layout
            $this->replaceWelcomePage($theme_folder);
        }

        $this->refreshThemes();

    }

    private function replaceWelcomePage($themeFolder)
    {
        $themeMarketingPath = resource_path("views/themes/{$themeFolder}/components/layouts/index.blade.php");
        $welcomePath = resource_path('views/welcome.blade.php');

        if (File::exists($themeMarketingPath)) {
            // Copy content from marketing.blade.php to welcome.blade.php
            File::put($welcomePath, File::get($themeMarketingPath));
        }

    }

    private function writeThemeJson($themeName)
    {
        $themeJsonPath = base_path('theme.json');
        $themeJsonContent = json_encode(['name' => $themeName], JSON_PRETTY_PRINT);
        File::put($themeJsonPath, $themeJsonContent);
    }

    private function deactivateThemes()
    {
        Theme::query()->update(['active' => 0]);
    }

    protected function publishAssets($themeFolder)
    {
        $assetsPath = resource_path("views/themes/$themeFolder/assets");

        if (File::isDirectory($assetsPath)) {
            $publicThemePath = public_path("themes/$themeFolder");

            // Create public theme directory if it doesn't exist
            if (! File::exists($publicThemePath)) {
                File::makeDirectory($publicThemePath, 0755, true, true);
            }

            // Copy assets to public directory
            File::copyDirectory($assetsPath, $publicThemePath);
        }
    }

    public function render()
    {
        // Use collect() to ensure we have a collection
        $themes = collect(Theme::all());

        return view('livewire.admin.theme.theme-manager', [
            'themes' => $themes,
        ]);
    }
}
