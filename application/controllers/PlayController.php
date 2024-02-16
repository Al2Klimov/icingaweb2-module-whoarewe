<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Controllers;

use Icinga\Module\Whoarewe\RedisAwareController;
use Icinga\Security\SecurityException;
use ipl\Html\Html;
use ipl\Html\ValidHtml;
use ipl\Web\Compat\CompatController;

class PlayController extends CompatController
{
    use RedisAwareController;

    public function indexAction(): void
    {
        $game = $this->params->getRequired('game');
        $redis = $this->getRedis();
        $state = $this->loadGame($redis, $game);

        if (! $state->started) {
            throw new SecurityException($this->translate('Game not started yet: %s'), $game);
        }

        $user = $this->Auth()->getUser()->getUsername();

        if (! array_key_exists($user, $state->players)) {
            throw new SecurityException($this->translate('You haven\'t joined game: %s'), $game);
        }

        $this->addContent(Html::tag('h2', $this->translate('Teams')));

        foreach ($state->teams as $team => $players) {
            $this->addContent(Html::tag('h3', sprintf($this->translate('Team %d'), $team)));

            $this->addContent(Html::tag('ul', [], array_map(
                function (string $name): ValidHtml {
                    return Html::tag('li', $name);
                },
                array_keys($players)
            )));
        }
    }
}
