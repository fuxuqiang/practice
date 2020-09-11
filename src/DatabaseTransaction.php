<?php

namespace Src;

trait DatabaseTransaction
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Mysql::begin();
    }

    public static function tearDownAfterClass(): void
    {
        Mysql::rollback();
    }
}
