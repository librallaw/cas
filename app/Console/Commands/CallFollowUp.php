<?php

namespace App\Console\Commands;

use App\Jobs\ProcessJob;
use Illuminate\Console\Command;
use App\Job;



class CallFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:follow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send call notifications to members of a church ';

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
        $current_time = time();

        //fetch jobs where run time has reached
        $caljobs  = Job::where("follow_type",'cfu')->where("status",0) -> where("run_time","<=",$current_time)->get();

        foreach ($caljobs as $jobi){
            //call nested dispatcher
            ProcessJob::dispatch($jobi);

        }


    }
}
