<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeamMember;

class TeamController extends Controller
{
    private const GROUPS = [
        'leadership'     => ['Leadership', 'The people steering the programme.'],
        'advisory_board' => ['Advisory board', 'Sector experts who guide our direction.'],
        'secretariat'    => ['Secretariat', 'The team delivering day to day.'],
    ];

    public function index(): void
    {
        $grouped = [];
        foreach (TeamMember::published() as $m) {
            $m['image_url'] = media_url($m['photo_media_id'] ?? null);
            $grouped[$m['group']][] = $m;
        }

        $sections = [];
        foreach (self::GROUPS as $key => [$label, $hint]) {
            if (!empty($grouped[$key])) {
                $sections[$key] = ['label' => $label, 'hint' => $hint, 'members' => $grouped[$key]];
            }
        }

        $this->render('pages.team', ['sections' => $sections, 'total' => array_sum(array_map(fn ($s) => count($s['members']), $sections))]);
    }
}
