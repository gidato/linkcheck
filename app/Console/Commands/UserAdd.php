<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Ramsey\Uuid\Uuid;

class UserAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add {name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user to the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $validator = Validator::make($this->arguments(), [
            'name' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return -1;
        }

        $user = new User();
        $user->name = $this->argument('name');
        $user->email = $this->argument('email');
        $user->password = Hash::make(Uuid::uuid4());
        $user->save();

        $this->info('User created - use password reset to gain access');
    }
}
