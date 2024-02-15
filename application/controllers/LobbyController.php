<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Whoarewe\Form\ConfirmForm;
use Icinga\Module\Whoarewe\Game;
use Icinga\Module\Whoarewe\RedisAwareController;
use Icinga\Security\SecurityException;
use ipl\Html\Html;
use ipl\Html\ValidHtml;
use ipl\Web\Compat\CompatController;
use ipl\Web\Url;

class LobbyController extends CompatController
{
    use RedisAwareController;

    protected $autorefreshInterval = 1;

    public function indexAction(): void
    {
        $game = $this->params->getRequired('game');
        $redis = $this->getRedis();
        $state = $this->loadGame($redis, $game);

        if ($state->started) {
            $this->redirectNow(Url::fromPath('whoarewe/play')->setParam('game', $game));
        }

        $join = Url::fromPath('whoarewe/join')->setParam('game', $game);

        $this->addContent(Html::tag('h2', $this->translate('Invite others')));
        $this->addContent(Html::tag('p', $this->translate('Right-click the link and select copy link location.')));
        $this->addContent(Html::tag('p', Html::tag('a', ['href' => $join], $join->getAbsoluteUrl())));

        if (count($state->players) > 1) {
            if (array_key_first($state->players) === $this->Auth()->getUser()->getUsername()) {
                $this->addContent(
                    (new ConfirmForm($this->translate('Start game')))
                        ->on(ConfirmForm::ON_SUCCESS, function () use ($redis, $game): void {
                            $this->updateGame($redis, $game, function (Game $state) use ($game): void {
                                if ($state->started) {
                                    throw new SecurityException($this->translate('Game already started: %s'), $game);
                                }

                                $state->started = true;
                            });

                            $this->redirectNow(Url::fromPath('whoarewe/play')->setParam('game', $game));
                        })
                        ->handleRequest(ServerRequest::fromGlobals())
                );
            }
        }

        $this->addContent(Html::tag('h2', $this->translate('Players')));

        $this->addContent(Html::tag('ul', [], array_map(
            function (string $name): ValidHtml {
                return Html::tag('li', $name);
            },
            array_keys($state->players)
        )));
    }
}
