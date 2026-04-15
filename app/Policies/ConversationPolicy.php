<?php

namespace App\Policies;

use App\Access\Controls\ConversationControl;
use Lomkit\Access\Policies\ControlledPolicy;

class ConversationPolicy extends ControlledPolicy
{
    protected string $control = ConversationControl::class;
}
