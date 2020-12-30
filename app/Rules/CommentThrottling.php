<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CommentThrottling implements Rule
{
    public function __construct(protected User $user)
    {
    }

    public function passes($attribute, $value) : bool {
        return $this->user->lastComment?->created_at->lt(
            now()->subMinutes(2)
        );
    }

    public function message() : string {
        return 'You are commenting too frequently.';
    }
}
