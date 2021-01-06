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
        if ($this->user->comments()->count() === 0) {
            return true;
        }

        $lastComment = $this->user->comments()->latest()->first();

        return $lastComment->created_at->lt(
            now()->subSeconds(5)
        );
    }

    public function message() : string {
        return 'You are commenting too frequently.';
    }
}
