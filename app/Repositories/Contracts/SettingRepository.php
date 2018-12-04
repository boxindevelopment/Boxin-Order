<?php

namespace App\Repositories\Contracts;

use App\Model\Setting;

interface SettingRepository
{
    public function create(array $data);

    public function update(Setting $sett, $data);

    public function delete(Setting $sett);
}
