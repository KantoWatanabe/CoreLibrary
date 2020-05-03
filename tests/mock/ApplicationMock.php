<?php
namespace mock;

use Kore\Application;

class ApplicationMock extends Application
{
    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getDefaultController()
    {
        return $this->defaultController;
    }

    public function parseControllerMock($path)
    {
        return $this->parseController($path);
    }

    public function parseCommandMock($argv)
    {
        return $this->parseCommand($argv);
    }
}
