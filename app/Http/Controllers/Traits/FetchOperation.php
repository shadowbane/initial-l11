<?php

namespace App\Http\Controllers\Traits;

/**
 * Replacement for Backpack's Default FetchOperation.
 * The default one gives us an error saying that
 * "Trait method 'setupFetchOperationDefaults' will not be applied because it collides with 'FetchOperation'"
 * so we replace it to make our code is linter friendly.
 */
trait FetchOperation
{
    use \Backpack\Pro\Http\Controllers\Operations\FetchOperation;
}
