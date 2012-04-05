<?php

namespace Spewia\Controller;

class ErrorController extends BaseController
{

    public function error404Action(\Exception $exception)
    {
        $this->response->setStatusCode(404);
    }

    public function error5xxAction(\Exception $exception)
    {
        $this->response->setStatusCode(500);
    }
}