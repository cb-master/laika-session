<?php
/**
 * Library Name: Cloud Bill Master PHP Session Handler
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com 
 */

namespace CBM\Session\Interface;

use SessionHandlerInterface;

// Session Driver Interface
Interface SessionDriverInterface extends SessionHandlerInterface
{
    public function setup(): void;
}