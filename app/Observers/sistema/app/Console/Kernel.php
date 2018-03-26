<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\IntegrateCustomerMailMarketing::class,
        Commands\CheckForOrdersNotIntegrated::class,
        Commands\MakeDefaultBundles::class,
        Commands\CheckForInvoiceNumber::class,
        Commands\CheckOrdersAuthorizeds::class,
        Commands\SendInterestedCustomersToEvolux::class,
        Commands\SendInterestedCustomersOfTheWeekToEvolux::class,
        Commands\SendIntegratedCustomersToEvoluxByBundle::class,
        Commands\EvoluxCampaignCleaning::class,
        Commands\AnalyzeOrder::class,
        Commands\UpdateCurrencyRates::class,
        Commands\ClearInterestedCampaignByCreatedDate::class,
        Commands\SendCustomersWithBilletOverdueToEvolux::class,
        Commands\AssignCommissions::class,
        Commands\SendCommissionsPostBack::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('marketing:integrate-customers')
                 ->everyTenMinutes()->withoutOverlapping();

        $schedule->command('orders:check-authorized')
                 ->dailyAt("00:00")->withoutOverlapping();

        $schedule->command('evolux:billet-overdue')
                 ->dailyAt("00:30")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=60')
                 ->dailyAt("01:00")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=120')
                 ->dailyAt("01:20")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=180')
                 ->dailyAt("01:40")->withoutOverlapping();

        $schedule->command('currency:update')
                 ->dailyAt("01:50")->withoutOverlapping();

        $schedule->command('orders:check-invoices')
                 ->dailyAt("02:00")->withoutOverlapping();

        $schedule->command('orders:integrate')
                  ->cron('* 5 * * 2-6')->withoutOverlapping();

        $schedule->command('evolux:campaign-cleaning --ExternalServiceSettings_id=3')
                 ->cron('30 20 * * 1-6')->withoutOverlapping();

        $schedule->command('evolux:campaign-cleaning --ExternalServiceSettings_id=8')
                 ->cron('35 20 * * 1-6')->withoutOverlapping();

        $schedule->command('evolux:campaign-cleaning --ExternalServiceSettings_id=1')
                 ->cron('40 20 * * 1-6')->withoutOverlapping();

        $schedule->command('evolux:campaign-cleaning --ExternalServiceSettings_id=7')
                 ->cron('50 20 * * 6')->withoutOverlapping();

        $schedule->command('evolux:interested-of-the-week')
                  ->cron('0 21 * * 5')->withoutOverlapping();

        $schedule->command('evolux:interested-cleaning')
                 ->dailyAt("21:30")->withoutOverlapping();

        $schedule->command('evolux:billet-overdue --remove')
                 ->dailyAt("23:25")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=60 --remove')
                 ->dailyAt("23:40")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=120 --remove')
                 ->dailyAt("23:45")->withoutOverlapping();

        $schedule->command('evolux:integrated-by-bundle --interval-days=180 --remove')
                 ->dailyAt("23:50")->withoutOverlapping();
    }
}
