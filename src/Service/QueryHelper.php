<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class QueryHelper
{

    public function params(Request $request, $keys = []): array    
    {
        $params = [];

        foreach ($keys as $key) {
            $params[$key] = $request->query->get($key);
        }

        return $params;
    }
}
