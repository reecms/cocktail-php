<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ree\Cocktail\Contracts;

/**
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
interface Mixer
{
    public function compile($source, $dest);
}
