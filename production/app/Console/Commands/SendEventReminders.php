<?php

namespace App\Console\Commands;

use App\Models\EventBooking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send 24-hour and 1-hour event reminder emails to booked users.';

    public function handle(): int
    {
        $now = now();

        $this->send24HourReminders($now);
        $this->send1HourReminders($now);

        $this->info('Event reminders processed.');

        return self::SUCCESS;
    }

    private function send24HourReminders(Carbon $now): void
    {
        $windowStart = $now->copy()->addHours(24)->subMinutes(5);
        $windowEnd = $now->copy()->addHours(24)->addMinutes(5);

        $bookings = $this->baseBookingQuery()
            ->whereNull('reminder_24h_sent_at')
            ->get()
            ->filter(function ($booking) use ($windowStart, $windowEnd) {
                $eventStart = $this->eventStartDateTime($booking);
                return $eventStart && $eventStart->between($windowStart, $windowEnd);
            });

        foreach ($bookings as $booking) {
            $this->sendReminderEmail($booking, 'emails.events.reminder-24h', 'Event Reminder: 24 Hours To Go');
            $booking->reminder_24h_sent_at = now();
            $booking->save();
        }
    }

    private function send1HourReminders(Carbon $now): void
    {
        $windowStart = $now->copy()->addHour()->subMinutes(5);
        $windowEnd = $now->copy()->addHour()->addMinutes(5);

        $bookings = $this->baseBookingQuery()
            ->whereNull('reminder_1h_sent_at')
            ->get()
            ->filter(function ($booking) use ($windowStart, $windowEnd) {
                $eventStart = $this->eventStartDateTime($booking);
                return $eventStart && $eventStart->between($windowStart, $windowEnd);
            });

        foreach ($bookings as $booking) {
            $this->sendReminderEmail($booking, 'emails.events.reminder-1h', 'Event Reminder: Starting Soon');
            $booking->reminder_1h_sent_at = now();
            $booking->save();
        }
    }

    private function baseBookingQuery()
    {
        return EventBooking::query()
            ->with(['event', 'user'])
            ->whereIn('status', ['confirmed', 'paid'])
            ->whereHas('event', function ($query) {
                $query->where('status', 'published');
            })
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email');
            });
    }

    private function eventStartDateTime(EventBooking $booking): ?Carbon
    {
        $event = $booking->event;
        if (!$event || !$event->start_date) {
            return null;
        }

        $date = $event->start_date->format('Y-m-d');
        $time = $event->start_time ?: '00:00';

        return Carbon::parse($date . ' ' . $time, config('app.timezone'));
    }

    private function sendReminderEmail(EventBooking $booking, string $view, string $subject): void
    {
        $user = $booking->user;
        $event = $booking->event;

        if (!$user || !$user->email || !$event) {
            return;
        }

        $eventStart = $this->eventStartDateTime($booking);

        $data = [
            'event' => $event,
            'user' => $user,
            'eventStart' => $eventStart,
            'meetingLink' => $event->meeting_link,
            'location' => $event->venue,
        ];

        Mail::send($view, $data, function ($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }
}
