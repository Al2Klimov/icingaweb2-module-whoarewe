<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Form;

use ipl\I18n\Translation;

class StartForm extends ConfirmForm
{
    use Translation;

    public function __construct()
    {
        parent::__construct($this->translate('Start game'));
    }

    protected function assemble(): void
    {
        $this->addElement('number', 'teams', [
            'label'    => $this->translate('Teams'),
            'required' => true,
            'min'      => 2
        ]);

        parent::assemble();
    }
}
