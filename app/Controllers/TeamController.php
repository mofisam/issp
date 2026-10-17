<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeamMember;

class TeamController extends Controller
{
    public function index(): void
    {
        $this->render('pages.team', ['members' => TeamMember::published()]);
    }
}
