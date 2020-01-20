<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Ramsey\Uuid\Uuid;

class UserRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a user from the database';

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
            'email' => 'required|email|exists:users'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return -1;
        }

        if (User::count() == 1) {
            if (!$this->confirm('This is the last user on the database. Do you wish to continue?')) {
                return 0;
            }
        }

        $user = User::where('email', $this->argument('email'));
        $user->delete();

        $this->info('User removed');
    }
}
