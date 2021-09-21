<?php

/*
 * This file is part of flarum/nickname.
 *
 * Copyright (c) 2020 Flarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use cccRaim\FlarumJHLogin\JHAuthController;
use Flarum\Extend;

return [

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Routes('forum'))
        ->post('/auth/jh', 'auth.jh', JHAuthController::class),

    (new Extend\View)
        ->namespace('cccRaim.flarum-jh-login', __DIR__.'/views'),
];
