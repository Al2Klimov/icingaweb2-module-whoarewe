<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Controllers;

use Icinga\Module\Whoarewe\RedisAwareController;
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

        $join = Url::fromPath('whoarewe/join')->setParam('game', $game);

        $this->addContent(Html::tag('h2', $this->translate('Invite others')));
        $this->addContent(Html::tag('p', $this->translate('Right-click the link and select copy link location.')));
        $this->addContent(Html::tag('p', Html::tag('a', ['href' => $join], $join->getAbsoluteUrl())));

        $this->addContent(Html::tag('h2', $this->translate('Players')));

        $this->addContent(Html::tag('ul', [], array_map(
            function (string $name): ValidHtml {
                return Html::tag('li', $name);
            },
            array_keys($state->players)
        )));
    }
}
