<?php

namespace Kazuki\LaravelWhereLike;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class WhereLikeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Builder::mixin(new BuilderMixin());
    }
}
