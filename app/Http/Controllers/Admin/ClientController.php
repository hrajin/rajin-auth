<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    public function __construct(private ClientRepository $clients) {}

    public function index(): View
    {
        $clients = Client::orderByDesc('created_at')->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'redirect_uri'          => ['required', 'url'],
            'confidential'          => ['sometimes', 'boolean'],
            'max_devices_per_user'  => ['nullable', 'integer', 'min:1', 'max:255'],
            'device_limit_strategy' => ['sometimes', 'in:block,evict_oldest'],
        ]);

        $confidential = $data['confidential'] ?? true;

        $client = $this->clients->create(
            userId: null,
            name: $data['name'],
            redirect: $data['redirect_uri'],
            provider: null,
            personalAccess: false,
            password: false,
            confidential: $confidential,
        );

        // Passport's ClientRepository doesn't expose max_devices_per_user,
        // so we set it directly after creation
        $client->forceFill([
            'max_devices_per_user'  => $data['max_devices_per_user'] ?? null,
            'device_limit_strategy' => $data['device_limit_strategy'] ?? 'block',
        ])->save();

        return redirect()->route('admin.clients.index')
            ->with('status', 'Client created successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->clients->delete($client);

        return redirect()->route('admin.clients.index')
            ->with('status', 'Client deleted.');
    }
}
