<?php

namespace App\Observers;

use App\Models\Setting;
use App\Support\HomePageCache;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    private function clearCache(): void
    {
        HomePageCache::invalidateSettings();
        Cache::forget('seo_auto_links_runtime');
    }

    /**
     * Handle the Setting "created" event.
     */
    public function created(Setting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Setting "updated" event.
     */
    public function updated(Setting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Setting "deleted" event.
     */
    public function deleted(Setting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Setting "restored" event.
     */
    public function restored(Setting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Setting "force deleted" event.
     */
    public function forceDeleted(Setting $setting): void
    {
        $this->clearCache();
    }
}
