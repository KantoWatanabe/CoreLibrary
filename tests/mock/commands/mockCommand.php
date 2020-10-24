<?php
namespace mock\commands;

use Kore\Command;

class mockCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $isError = $this->getOpt('error');
        if ($isError === '1') {
            throw new \Exception('test error!');
        }
    }
}
