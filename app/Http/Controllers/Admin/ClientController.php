<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'logout_uri'            => ['nullable', 'url'],
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

        $logoutSecret = null;

        if (!empty($data['logout_uri'])) {
            $logoutSecret = Str::random(40);
        }

        $client->forceFill([
            'logout_uri'            => $data['logout_uri'] ?? null,
            'logout_secret'         => $logoutSecret,
            'max_devices_per_user'  => $data['max_devices_per_user'] ?? null,
            'device_limit_strategy' => $data['device_limit_strategy'] ?? 'block',
        ])->save();

        return redirect()->route('admin.clients.index')
            ->with('toast_success', 'Client created successfully.')
            ->with('logout_secret', $logoutSecret);
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->clients->delete($client);

        return redirect()->route('admin.clients.index')
            ->with('toast_success', 'Client deleted.');
    }
}
