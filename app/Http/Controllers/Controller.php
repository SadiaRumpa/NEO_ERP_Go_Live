<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    public $mimetypes = '';

    public function __construct(){
        $appbrand = view()->shared('appbrand');
        $this->mimetypes = $appbrand ? $appbrand->getAllowedFileType('IMG', true) : '';
    }

    /**
     * Get validated per-page count from request or default.
     */
    protected function getPerPage(int $default = 10, array $allowed = [5, 10, 25, 50, 100]): int
    {
        $perPage = (int) request()->input('per_page', $default);
        if (!in_array($perPage, $allowed)) {
            $perPage = $default;
        }
        return $perPage;
    }
}
