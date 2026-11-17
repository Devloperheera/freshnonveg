<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\CashfreeService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = collect(PaymentGateway::CODES)
            ->mapWithKeys(fn (string $code) => [$code => $this->gateway($code)]);

        $active = PaymentGateway::active();

        return view('Admin.PaymentGateways.index', [
            'gateways' => $gateways,
            'active' => $active,
            'webhooks' => [
                PaymentGateway::CASHFREE => route('checkout.cashfree.webhook'),
                PaymentGateway::RAZORPAY => route('checkout.razorpay.webhook'),
            ],
        ]);
    }

    public function update(Request $request, string $gateway): RedirectResponse
    {
        abort_unless(in_array($gateway, PaymentGateway::CODES, true), 404);
        $model = $this->gateway($gateway);

        $data = $request->validate([
            'key_id' => ['nullable', 'string', 'max:191'],
            'key_secret' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'environment' => ['required', 'in:sandbox,production'],
            'is_active' => ['nullable', 'boolean'],
            'clear_keys' => ['nullable', 'boolean'],
        ], [], [
            'key_id' => $gateway === PaymentGateway::RAZORPAY ? 'Key ID' : 'App ID',
            'key_secret' => $gateway === PaymentGateway::RAZORPAY ? 'Key Secret' : 'Secret Key',
        ]);

        if ($request->boolean('clear_keys')) {
            $model->key_id = null;
            $model->key_secret = null;
            $model->webhook_secret = null;
            $model->is_active = false;
            $model->environment = $data['environment'];
            $model->save();

            return back()->with('success', $model->name.' keys hata di gayi.');
        }

        $keyId = trim((string) ($data['key_id'] ?? ''));
        if ($keyId !== '') {
            $model->key_id = $keyId;
        }

        $secret = trim((string) ($data['key_secret'] ?? ''));
        if ($secret !== '') {
            $model->key_secret = $secret;
        }

        $webhook = trim((string) ($data['webhook_secret'] ?? ''));
        if ($webhook !== '') {
            $model->webhook_secret = $webhook;
        }

        $model->environment = $data['environment'];

        if ($gateway === PaymentGateway::RAZORPAY && filled($model->key_id)) {
            $isLive = str_starts_with((string) $model->key_id, 'rzp_live_');
            if ($isLive !== ($model->environment === 'production')) {
                return back()->withInput()->withErrors([
                    'razorpay.environment' => 'Razorpay Key ID '.($isLive ? 'LIVE (rzp_live_)' : 'TEST (rzp_test_)').' hai, environment bhi '.($isLive ? 'Production' : 'Sandbox / Test').' chunein.',
                ])->with('open_gateway', $gateway);
            }
        }

        $wantActive = $request->boolean('is_active');
        if ($wantActive && ! $model->isConfigured()) {
            return back()->withInput()->withErrors([
                $gateway.'.key_id' => $model->name.' active karne ke liye pehle dono keys daalein.',
            ])->with('open_gateway', $gateway);
        }

        DB::transaction(function () use ($model, $wantActive) {
            if ($wantActive) {
                PaymentGateway::query()->whereKeyNot($model->id)->update(['is_active' => false]);
            }
            $model->is_active = $wantActive;
            $model->save();
        });

        $message = $model->name.' settings save ho gayi.';
        if ($wantActive) {
            $message .= ' Ab website/app checkout '.$model->name.' se hoga.';
        } elseif (! PaymentGateway::active()) {
            $message .= ' Dhyan dein: koi gateway active nahi hai, checkout band rahega.';
        }

        return back()->with('success', $message);
    }

    public function activate(string $gateway): RedirectResponse
    {
        abort_unless(in_array($gateway, PaymentGateway::CODES, true), 404);
        $model = $this->gateway($gateway);

        if (! $model->isConfigured()) {
            return back()->withErrors([
                $gateway.'.key_id' => $model->name.' ki keys set nahi hain. Pehle keys save karein.',
            ])->with('open_gateway', $gateway);
        }

        DB::transaction(function () use ($model) {
            PaymentGateway::query()->whereKeyNot($model->id)->update(['is_active' => false]);
            $model->update(['is_active' => true]);
        });

        return back()->with('success', $model->name.' active ho gaya. Ab saare naye payments '.$model->name.' se honge.');
    }

    public function deactivate(string $gateway): RedirectResponse
    {
        abort_unless(in_array($gateway, PaymentGateway::CODES, true), 404);
        $model = $this->gateway($gateway);
        $model->update(['is_active' => false]);

        return back()->with('success', $model->name.' inactive ho gaya.'.(PaymentGateway::active() ? '' : ' Koi gateway active nahi hai — checkout band rahega.'));
    }

    public function test(string $gateway, CashfreeService $cashfree, RazorpayService $razorpay): RedirectResponse
    {
        abort_unless(in_array($gateway, PaymentGateway::CODES, true), 404);
        $model = $this->gateway($gateway);

        if (! $model->isConfigured()) {
            return back()->withErrors([
                $gateway.'.key_id' => 'Test se pehle '.$model->name.' keys save karein.',
            ])->with('open_gateway', $gateway);
        }

        $result = $gateway === PaymentGateway::RAZORPAY
            ? $razorpay->testCredentials((string) $model->key_id, (string) $model->key_secret)
            : $cashfree->testCredentials((string) $model->key_id, (string) $model->key_secret, $model->isProduction());

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withErrors([$gateway.'.test' => $result['message']])->with('open_gateway', $gateway);
    }

    private function gateway(string $code): PaymentGateway
    {
        return PaymentGateway::query()->firstOrCreate(
            ['code' => $code],
            ['name' => PaymentGateway::label($code), 'is_active' => false, 'environment' => 'sandbox']
        );
    }
}
