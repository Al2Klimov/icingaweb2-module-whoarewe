<?php

// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Icinga\Module\Whoarewe\Form;

use ipl\I18n\Translation;

class AssignForm extends ConfirmForm
{
    use Translation;

    public function __construct()
    {
        parent::__construct($this->translate('Assign'));
    }

    protected function assemble(): void
    {
        $this->addElement('text', 'identity', [
            'label'    => $this->translate('Identity'),
            'required' => true
        ]);

        parent::assemble();
    }
}
