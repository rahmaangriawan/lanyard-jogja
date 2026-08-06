<?php

namespace App\Observers;

use App\Models\Testimonial;
use App\Support\HomePageCache;

class TestimonialObserver
{
    public function created(Testimonial $testimonial): void
    {
        HomePageCache::invalidateTestimonials();
    }

    public function updated(Testimonial $testimonial): void
    {
        HomePageCache::invalidateTestimonials();
    }

    public function deleted(Testimonial $testimonial): void
    {
        HomePageCache::invalidateTestimonials();
    }

    public function restored(Testimonial $testimonial): void
    {
        HomePageCache::invalidateTestimonials();
    }

    public function forceDeleted(Testimonial $testimonial): void
    {
        HomePageCache::invalidateTestimonials();
    }
}
