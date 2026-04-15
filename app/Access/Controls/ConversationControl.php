<?php

namespace App\Access\Controls;

use App\Access\Perimeters\GlobalPerimeter;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Lomkit\Access\Controls\Control;

class ConversationControl extends Control
{
     /**
      * The model the control refers to.
      * @var class-string<Model>
      */
     protected string $model = Conversation::class;

    /**
     * Retrieve the list of perimeter definitions for the current control.
     *
     * @return array<\Lomkit\Access\Perimeters\Perimeter> An array of Perimeter objects.
     */
    protected function perimeters(): array
    {
        return [
            GlobalPerimeter::new()
                ->allowed(function (Model $user, string $method) {
	                return $user->can(sprintf('%s_conversations', $method));
		            })
		            ->should(function (Model $user, Model $activityLog) {
		                return true;
		            })
		            ->query(function (Builder $query, Model $user) {
		                return $query;
		            }),
        ];
    }
}
