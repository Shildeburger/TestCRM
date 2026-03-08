<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Contact::query()->ownedBy($user);

        //Фильтры
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($country = $request->string('country')->toString()) {
            $query->where('country', $country);
        }

        if ($request->boolean('favorite')) {
            $query->where('is_favorite', true);
        }

        //Сортировка
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if (! in_array($sort, ['name', 'company', 'country'], true)) {
            $sort = 'name';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query->orderBy($sort, $direction);

        $contacts = $query
            ->paginate(10)
            ->withQueryString()
            ->through(function (Contact $contact) {
                return [
                    'id'          => $contact->id,
                    'name'        => $contact->name,
                    'email'       => $contact->email,
                    'phone'       => $contact->phone,
                    'company'     => $contact->company,
                    'country'     => $contact->country,
                    'is_favorite' => $contact->is_favorite
                ];
            });
        
        //Список стран (можно брать из БД distinct)
        $countries = Contact::ownedBy($user)
            ->select('country')
            ->whereNotNull('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->values();
        
        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters'  => [
                'search'    => $search,
                'country'   => $country,
                'favorite'  => $request->boolean('favorite'),
                'sort'      => $sort,
                'direction' => $direction
            ],
            'countries' => $countries
        ]);
    }

    public function create() {
        return Inertia::render('Contacts/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'company'     => ['nullable', 'string', 'max:255'],
            'job_title'   => ['nullable', 'string', 'max:255'],
            'country'     => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
            'is_favorite' => ['boolean']
        ]);

        $data['user_id'] = Auth::id();
        $data['is_favorite'] = $request->boolean('is_favorite');

        Contact::create($data);

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact created.');
    }

    public function edit (Contact $contact) {
        $this->authorizeContact($contact);

        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id'          => $contact->id,
                'name'        => $contact->name,
                'email'       => $contact->email,
                'phone'       => $contact->phone,
                'company'     => $contact->company,
                'job_title'   => $contact->job_title,
                'country'     => $contact->country,
                'notes'       => $contact->notes,
                'is_favorite' => $contact->is_favorite
            ]
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $this->authorizeContact($contact);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'company'     => ['nullable', 'string', 'max:255'],
            'job_title'   => ['nullable', 'string', 'max:255'],
            'country'     => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
            'is_favorite' => ['boolean']
        ]);

        $data['is_favorite'] = $request->boolean('is_favorite');

        $contact->update($data);

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $this->authorizeContact($contact);

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact deleted.');
    }

    public function toggleFavorite(Contact $contact)
    {
        $this->authorizeContact($contact);

        $contact->update([
            'is_favorite' => ! $contact->is_favorite
        ]);

        return back()->with('success', 'Favorite updated.');
    }

    protected function authorizeContact(Contact $contact): void
    {
        abort_unless($contact->user_id === Auth::id(), 403);
    }

    public function show(Contact $contact)
    {
        $this->authorizeContact($contact);

        $chat = TelegramChat::where('contact_id', $contact->id)
            ->where('is_primary', true)
            ->first();

        $messages = [];

        if ($chat) {
            $messages = TelegramMessage::where('telegram_chat_id', $chat->id)
                ->orderBy('sent_at')
                ->get()
                ->map(fn (TelegramMessage $message) => [
                    'id'               => $message->id,
                    'telegram_chat_id' => $message->telegram_chat_id,
                    'text'             => $message->text,
                    'direction'        => $message->direction,
                    'from_role'        => $message->from_role,
                    'sent_at'          => $message->sent_at?->toIso8601String(),
                ]);
        }

        return Inertia::render('Contacts/Show', [
            'contact' => [
                'id'                => $contact->id,
                'name'              => $contact->name,
                'email'             => $contact->email,
                'phone'             => $contact->phone,
                'company'           => $contact->company,
                'job_title'         => $contact->job_title,
                'country'           => $contact->country,
                'notes'             => $contact->notes,
                'is_favorite'       => $contact->is_favorite,
                'telegram_user_id'  => $contact->telegram_user_id,
                'telegram_username' => $contact->telegram_username,
            ],
            'chat' => $chat ? [
                'id'             => $chat->id,
                'type'           => $chat->type,
                'chat_title'     => $chat->chat_title,
                'chat_username'  => $chat->chat_username,
                'chat_external_id' => $chat->chat_external_id,
            ] : null,
            'messages' => $messages,
        ]);
    }
}