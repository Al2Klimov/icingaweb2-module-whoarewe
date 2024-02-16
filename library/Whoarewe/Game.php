<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe;

class Game
{
    const EXPIRE = 3600;

    public array $players = [];
    public bool $started = false;
    public array $teams = [];
    public array $identities = [];
}
