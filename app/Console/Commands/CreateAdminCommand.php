<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature   = 'create:admin';
    protected $description = 'Create a new Admin for FreshNonVeg Admin Panel';

    public function handle(): void
    {
        $this->newLine();
        $this->line('<fg=blue>
    ╔══════════════════════════════════════════════════════════╗
    ║                                                          ║
    ║              ✨ FreshNonVeg — Admin Setup ✨              ║
    ╚══════════════════════════════════════════════════════════╝</>');
        $this->newLine();

        // ── Name ──
        $name = $this->ask('<fg=blue>👤 Admin ka naam kya hai?</>');

        if (empty(trim($name))) {
            $this->error('❌ Naam khali nahi ho sakta!');
            return;
        }

        // ── Email with validation ──
        $email = $this->ask('<fg=blue>📧 Admin ki email kya hai?</>');

        $emailCheck = Validator::make(
            ['email' => $email],
            ['email' => 'required|email|unique:admins,email']
        );

        if ($emailCheck->fails()) {
            $this->error('❌ ' . $emailCheck->errors()->first('email'));
            return;
        }

        // ── Phone ──
        $phone = $this->ask('<fg=blue>📱 Phone number (optional, Enter skip karo)</>');

        // ── Password ──
        $password = $this->secret('<fg=blue>🔒 Password kya rakha chahte ho? (min: 6 characters)</>');

        if (strlen($password) < 6) {
            $this->error('❌ Password kam se kam 6 characters ka hona chahiye!');
            return;
        }

        // ── Confirm Password ──
        $confirmPassword = $this->secret('<fg=blue>🔒 Password dobara enter karo (confirm)</>');

        if ($password !== $confirmPassword) {
            $this->error('❌ Passwords match nahi kar rahe!');
            return;
        }

        // ── Status ──
        $statusChoice = $this->choice(
            '🟢 Admin ka status kya hoga?',
            ['active', 'inactive'],
            0
        );

        // ── Confirm Table ──
        $this->newLine();
        $this->line('<fg=blue>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=bright-blue>          📋 Admin Details Preview</>');
        $this->line('<fg=blue>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');

        $this->table(
            ['Field', 'Value'],
            [
                ['👤 Name',   $name],
                ['📧 Email',  $email],
                ['📱 Phone',  $phone ?: 'N/A'],
                ['🟢 Status', strtoupper($statusChoice)],
            ]
        );

        if (!$this->confirm('<fg=yellow>⚡ Upar di gayi details se admin create karna chahte ho?</>', true)) {
            $this->warn('⚠️  Operation cancelled. Koi admin create nahi hua.');
            return;
        }

        // ── Create Admin ──
        $admin = Admin::create([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone ?: null,
            'password' => Hash::make($password),
            'status'   => $statusChoice === 'active' ? 1 : 0, // ✅ Store as integer
        ]);

        $this->newLine();
        $this->line('<fg=blue>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->info('✅  Admin successfully create ho gaya! 🎉 FreshNonVeg');
        $this->line('<fg=blue>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');

        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'Status', 'Created At'],
            [[
                $admin->id,
                $admin->name,
                $admin->email,
                $admin->phone ?? 'N/A',
                $admin->status == 1 ? '✅ Active' : '❌ Inactive',
                $admin->created_at->format('d M Y, h:i A'),
            ]]
        );

        $this->newLine();
        $baseUrl = rtrim(config('app.url'), '/');
        $this->line("<fg=bright-blue>🔑 Ab login karo: {$baseUrl}/FreshNonVegAdmin/login</>");
        $this->line("<fg=bright-blue>📊 Dashboard: {$baseUrl}/FreshNonVegAdmin/dashboard</>");
        $this->newLine();
    }
}
