<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'creates a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user['name']     = $this->ask('Enter the user name');
        $user['email']    = $this->ask('Enter the user email');
        $user['password'] = $this->secret('Enter the user password');

        $roleName = $this->choice('Role of the user', ['admin', 'editor'], 1);
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error('Role not found.');
            return -1;
        }

        $validator = Validator::make($user, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'max:255', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return -1;
        }

        DB::transaction(function () use ($user, $role) {
            $user['password'] = Hash::make($user['password']);
            $newUser          = User::create($user);
            $newUser->roles()->attach($role->id);
        });

        $this->info('User created successfully.');
    }
}
