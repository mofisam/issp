<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Partner;

class PartnerController extends Controller
{
    public function index(): void
    {
        $partners = Partner::published();
        foreach ($partners as &$partner) {
            $partner['logo_url'] = media_url($partner['logo_media_id'] ?? null);
        }
        unset($partner);

        $this->render('pages.partners', ['partners' => $partners]);
    }
}
