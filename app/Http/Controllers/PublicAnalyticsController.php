<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisitorSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Agent;

class PublicAnalyticsController extends Controller
{
    public function collect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_token' => ['required', 'string', 'max:80'],
            'session_token' => ['required', 'string', 'max:80'],
            'event_name' => ['required', 'string', 'max:60'],
            'page_path' => ['nullable', 'string', 'max:255'],
            'page_name' => ['nullable', 'string', 'max:255'],
            'section_key' => ['nullable', 'string', 'max:120'],
            'label' => ['nullable', 'string', 'max:255'],
            'searched_term' => ['nullable', 'string', 'max:160'],
            'referrer' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $now = now();
        $agent = new Agent();
        $agent->setUserAgent((string) $request->userAgent());

        $session = AnalyticsVisitorSession::query()->firstOrNew([
            'session_token' => $data['session_token'],
        ]);

        if (! $session->exists) {
            $session->visitor_token = $data['visitor_token'];
            $session->first_seen_at = $now;
        }

        $session->fill([
            'visitor_token' => $data['visitor_token'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->resolveDeviceType($agent),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'current_path' => $data['page_path'] ?? null,
            'current_title' => $data['page_name'] ?? null,
            'referrer' => $data['referrer'] ?? null,
            'is_online' => true,
            'last_seen_at' => $now,
        ]);
        $session->save();

        if ($data['event_name'] !== 'heartbeat') {
            AnalyticsEvent::query()->create([
                'visitor_token' => $data['visitor_token'],
                'session_token' => $data['session_token'],
                'event_name' => $data['event_name'],
                'page_path' => $data['page_path'] ?? null,
                'page_name' => $data['page_name'] ?? null,
                'section_key' => $data['section_key'] ?? null,
                'label' => $data['label'] ?? null,
                'searched_term' => $data['searched_term'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'occurred_at' => $now,
            ]);
        }

        AnalyticsVisitorSession::query()
            ->where('last_seen_at', '<', Carbon::now()->subMinutes(5))
            ->update(['is_online' => false]);

        return response()->json([
            'ok' => true,
            'online_now' => AnalyticsVisitorSession::query()
                ->where('is_online', true)
                ->where('last_seen_at', '>=', Carbon::now()->subMinutes(5))
                ->count(),
        ]);
    }

    protected function resolveDeviceType(Agent $agent): string
    {
        if ($agent->isTablet()) {
            return 'tablet';
        }

        if ($agent->isMobile()) {
            return 'mobile';
        }

        return 'desktop';
    }
}
