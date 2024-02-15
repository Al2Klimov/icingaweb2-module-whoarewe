<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Whoarewe\Form\ConfirmForm;
use Icinga\Module\Whoarewe\Game;
use Icinga\Module\Whoarewe\RedisAwareController;
use ipl\Web\Compat\CompatController;
use ipl\Web\Url;

class JoinController extends CompatController
{
    use RedisAwareController;

    public function indexAction(): void
    {
        $game = $this->params->getRequired('game');

        $this->addContent(
            (new ConfirmForm($this->translate('Join game')))
                ->on(ConfirmForm::ON_SUCCESS, function () use ($game): void {
                    $this->updateGame($this->getRedis(), $game, function (Game $state) use ($game): void {
                        $state->players[$this->Auth()->getUser()->getUsername()] = null;
                    });

                    $this->redirectNow(Url::fromPath('whoarewe/lobby')->setParam('game', $game));
                })
                ->handleRequest(ServerRequest::fromGlobals())
        );
    }
}
