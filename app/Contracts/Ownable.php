<?php

namespace App\Contracts;

interface Ownable
{
    /**
     * Dapatkan ID user pemilik dari baris data ini.
     *
     * @return int|string|null
     */
    public function getOwnerId();
}
