<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Whoarewe\Form\AssignForm;
use Icinga\Module\Whoarewe\Game;
use Icinga\Module\Whoarewe\RedisAwareController;
use Icinga\Security\SecurityException;
use ipl\Html\Html;
use ipl\Html\ValidHtml;
use ipl\Web\Compat\CompatController;
use ipl\Web\Url;

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

        if (count($state->identities) < count($state->teams)) {
            $next = ($state->players[$user] + 1) % count($state->teams);

            if (array_key_exists($next, $state->identities)) {
                $this->addContent(Html::tag('p', $this->translate('Waiting for the other teams...')));
                $this->autorefreshInterval = 1;
            } else {
                $this->addContent(Html::tag('p', sprintf($this->translate(
                    "Assign team %d an identity they will have to guess! Consult your own team. E.g.:"
                ), $next)));

                $this->addContent(Html::tag('ul', [], [
                    Html::tag('li', $this->translate('The A-Team')),
                    Html::tag('li', $this->translate('Modern Talking')),
                    Html::tag('li', $this->translate('Tom & Jerry'))
                ]));

                $this->addContent(
                    (new AssignForm())
                        ->on(AssignForm::ON_SUCCESS, function (AssignForm $form) use ($redis, $game, $next): void {
                            $this->updateGame($redis, $game, function (Game $state) use ($form, $game, $next): void {
                                if (array_key_exists($next, $state->identities)) {
                                    throw new SecurityException($this->translate('Identity already assigned'));
                                }

                                $state->identities[$next] = $form->getValue('identity');
                            });

                            $this->redirectNow(Url::fromPath('whoarewe/play')->setParam('game', $game));
                        })
                        ->handleRequest(ServerRequest::fromGlobals())
                );
            }
        } else {
            $this->addContent(Html::tag('p', $this->translate(
                'Take turns, consult your own team and guess your team identity!'
            )));
        }

        $this->addContent(Html::tag('h2', $this->translate('Teams')));

        foreach ($state->teams as $team => $players) {
            $this->addContent(Html::tag('h3', sprintf($this->translate('Team %d'), $team)));

            if ($team != $state->players[$user] && isset($state->identities[$team])) {
                $this->addContent(Html::tag('p', $state->identities[$team]));
            }

            $this->addContent(Html::tag('ul', [], array_map(
                function (string $name): ValidHtml {
                    return Html::tag('li', $name);
                },
                array_keys($players)
            )));
        }
    }
}
