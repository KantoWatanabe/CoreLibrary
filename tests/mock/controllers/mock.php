<?php
namespace mock\controllers;

use Kore\Controller;

class mock extends Controller
{
    /**
     * {@inheritdoc}
     */
    protected function action()
    {
        $isError = $this->getQuery('error');
        if ($isError === '1') {
            throw new \Exception('test error!');
        }
    }
}
