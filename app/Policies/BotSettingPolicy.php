<?php

namespace App\Policies;

use App\Models\BotSetting;
use App\Models\User;

class BotSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bot_setting.view_any');
    }

    public function update(User $user, BotSetting $botSetting): bool
    {
        return $user->can('bot_setting.update');
    }
}
