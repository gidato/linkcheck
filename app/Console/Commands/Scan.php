<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\Value\Url;
use App\Support\Value\EmailOption;
use App\Site;
use InvalidArgumentException;
use App\Support\Service\Scan\NewScanGenerator;
use App\Jobs\ProcessScan;
use App\Jobs\SendEmail;

class Scan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:new
                            {url : The site url to do a scan for}
                            {--E|email= : Who to send emails to. Either SELF or ALL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers a new scan for a given site.';

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
    public function handle(NewScanGenerator $generator)
    {
        try {
            $url = new Url($this->argument('url'));
            $site = Site::where('url', (string) $url)->first();
            if (!$site) {
                throw new InvalidArgumentException('This site has not been setup.');
            }

            $email = new EmailOption($this->option('email'));

            $scan = $generator->generateScan($site);
            ProcessScan::withChain([
                new SendEmail($scan, $email)
            ])->dispatch($scan);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return -1;
        }
    }
}
