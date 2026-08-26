<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    /** Program akan datang, boleh ditapis. */
    public function index(Request $request)
    {
        $events = Event::upcoming()
            ->with(['category', 'speaker', 'venue', 'state', 'district'])
            ->when($request->filled('negeri'), fn ($q) => $q->whereHas(
                'state', fn ($s) => $s->where('slug', $request->string('negeri'))))
            ->when($request->filled('kategori'), fn ($q) => $q->whereHas(
                'category', fn ($c) => $c->where('slug', $request->string('kategori'))))
            ->when($request->string('harga')->value() === 'percuma',
                fn ($q) => $q->where('pricing_mode', '!=', 'berbayar'))
            ->paginate(9)->withQueryString();

        return view('public.events', [
            'events' => $events,
            'states' => $this->filterStates(),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }

    /** Jejak Jelajah — arkib program yang telah selesai. */
    public function past(Request $request)
    {
        $events = Event::completed()
            ->with(['category', 'speaker', 'venue', 'state', 'district'])
            ->withCount(['gallery', 'certificates'])
            ->when($request->filled('negeri'), fn ($q) => $q->whereHas(
                'state', fn ($s) => $s->where('slug', $request->string('negeri'))))
            ->when($request->filled('kategori'), fn ($q) => $q->whereHas(
                'category', fn ($c) => $c->where('slug', $request->string('kategori'))))
            ->paginate(9)->withQueryString();

        return view('public.trail', [
            'events' => $events,
            'states' => $this->filterStates(),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }

    /** Halaman program yang dijana automatik. */
    public function show(State $state, Event $event)
    {
        abort_unless($event->state_id === $state->id, 404);
        abort_unless($event->published_at !== null, 404);

        $event->load([
            'category', 'speaker', 'venue', 'state', 'district',
            'mobilizers', 'gallery' => fn ($q) => $q->approved()->ordered(),
            'testimonials' => fn ($q) => $q->approved(),
        ]);

        return view('public.event', [
            'event' => $event,
            'related' => Event::upcoming()
                ->where('id', '!=', $event->id)
                ->where(fn ($q) => $q->where('state_id', $event->state_id)
                    ->orWhere('event_category_id', $event->event_category_id))
                ->with(['venue', 'state', 'district', 'category'])
                ->limit(3)->get(),
        ]);
    }

    /** Pautan pendek /j/BDJ1026 */
    public function short(string $shortCode)
    {
        $event = Event::where('short_code', $shortCode)->firstOrFail();

        return redirect()->to($event->publicUrl(), 301);
    }

    /** Borang pendaftaran peserta. */
    public function register(State $state, Event $event)
    {
        abort_unless($event->state_id === $state->id, 404);
        abort_unless($event->published_at !== null, 404);

        return view('public.register', [
            'event' => $event->load(['venue', 'state', 'district', 'speaker', 'category']),
        ]);
    }

    /**
     * Hanya negeri yang mempunyai program — mengelak penapis kosong.
     * Kita cache id sahaja; model Eloquent tidak sesuai disimpan dalam
     * cache pangkalan data kerana null-byte pada sifat protected.
     */
    private function filterStates()
    {
        $ids = Cache::remember('filter.state_ids', 300, fn (): array => State::whereHas('events',
            fn ($q) => $q->whereNotNull('published_at'))->orderBy('sort_order')->pluck('id')->all());

        return State::whereIn('id', $ids)->orderBy('sort_order')->get();
    }
}
