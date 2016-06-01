<?php
namespace Numesia\NUAuth\Facades;

use Illuminate\Support\Facades\Facade;

class NUAuthFacade extends Facade {
    protected static function getFacadeAccessor() { return 'NUAuth'; }
}
