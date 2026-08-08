<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('library:mark-overdue')->dailyAt('07:00');
Schedule::command('fee:generate-monthly')->monthlyOn(1, '06:00');
Schedule::command('fee:send-reminders --days=3')->dailyAt('08:00');
Schedule::command('subscription:send-reminders --days=7')->weeklyOn(1, '09:00');
Schedule::command('subscription:send-reminders --days=3')->dailyAt('09:00');

// ===== Phase 8-11 schedulers =====
Schedule::command('daily-reports:generate')->weekdays()->dailyAt('17:00');
Schedule::command('payments:reconcile --batch=100')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('analytics:risk-scores')->dailyAt('01:00')->withoutOverlapping(60);
Schedule::command('analytics:predict-dropout')->weeklyOn(1, '06:00')->withoutOverlapping(120);
Schedule::command('transport:prune-locations --days=7')->dailyAt('02:00');
Schedule::command('ppdb:expire-drafts --days=30')->dailyAt('03:00');
Schedule::command('religious:monthly-report')->monthlyOn(1, '04:00');

// Daily DB backup at 2:30am
Schedule::command('sikadpro:backup --encrypt')->dailyAt('02:30')->withoutOverlapping();

// Emergency alerts — process draft queue every minute
Schedule::command('emergency:send --batch=20')->everyMinute()->withoutOverlapping();

// IndexNow SEO — submit URLs daily at 02:45
Schedule::command('seo:indexnow --new')->dailyAt('02:45')->withoutOverlapping();

// Benchmark — compute cross-school metrics on the 1st of every month at 5 AM
Schedule::command('benchmark:compute')->monthlyOn(1, '05:00')->withoutOverlapping();

// Tracer Study — send invitation to alumni 1 & 3 years after graduation
Schedule::command('tracer:send-invitations')->monthlyOn(1, '10:00');

// WhatsApp Bot — expire old sessions daily
Schedule::command('wa-bot:expire-sessions')->dailyAt('03:00');

// SPP Reminder Scheduler — send reminders daily at 07:00
Schedule::command('reminders:send')->dailyAt('07:00');
