<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Http\Controllers;

use App\Modules\Common\Infrastructure\Traits\ApiResponseTrait;
use Illuminate\Routing\Controller;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;
}
